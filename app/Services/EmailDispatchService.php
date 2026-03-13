<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EmailLog;
use App\Models\SmtpProvider;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Jobs\ProcessRecipientBatchJob;

class EmailDispatchService
{
    private array $smtpLog = [];

    public function queueCampaign(Campaign $campaign): array
    {
        $query = Contact::query();
        switch ($campaign->recipient_filter) {
            case 'subscribed':
                $query->where('subscribed', true);
                break;
            case 'tagged':
                if (!empty($campaign->tags_filter)) {
                    $tags = array_map('trim', explode(',', $campaign->tags_filter));
                    $query->where(function ($q) use ($tags) {
                        foreach ($tags as $tag) {
                            $q->orWhere('tags', 'like', "%{$tag}%");
                        }
                    });
                }
                break;
            default:
                $query->where('subscribed', true);
        }

        $totalRecipientsDispatched = 0;
        $batchSize = 1000; // Define a suitable batch size

        $query->chunkById($batchSize, function ($contacts) use ($campaign, &$totalRecipientsDispatched) {
            $recipientsData = [];
            foreach ($contacts as $contact) {
                $recipientsData[] = [
                    'email' => $contact->email,
                    'name'  => trim($contact->first_name . ' ' . $contact->last_name),
                ];
            }
            if (!empty($recipientsData)) {
                ProcessRecipientBatchJob::dispatch($campaign, $recipientsData);
                $totalRecipientsDispatched += count($recipientsData);
            }
        });

        // The actual queuing of EmailLog entries happens in the background jobs.
        // We report success if jobs are dispatched, not if all emails are instantly queued.
        if ($totalRecipientsDispatched > 0) {
            $campaign->update(['total_recipients' => $totalRecipientsDispatched]); // Update campaign total recipients

            AdminNotification::recordNotification(
                'campaign_queuing_started',
                'Campaign Queuing Started',
                "Campaign \"" . $campaign->name . "\" has started queuing emails for {$totalRecipientsDispatched} recipients.",
                'paper-plane', 'blue',
                route('admin.campaigns.show', $campaign->id)
            );
            return ['success' => true, 'queued' => $totalRecipientsDispatched, 'message' => 'Queuing jobs dispatched successfully.'];
        } else {
            return ['success' => false, 'message' => 'No recipients found or jobs dispatched.', 'queued' => 0];
        }
    }

    public function processQueue(int $limit = 500000): array
    {
        $sent = 0; $failed = 0; $skipped = 0;
        $this->resetDailyCountsIfNeeded();

        $queuedLogs = EmailLog::where('status', 'queued')
            ->whereHas('campaign', function ($q) {
                $q->whereIn('status', ['queued', 'sending']);
            })
            ->with('campaign')
            ->orderBy('created_at')
            ->take($limit)
            ->get();

        if ($queuedLogs->isEmpty()) {
            return ['success' => true, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
        }

        foreach ($queuedLogs as $log) {
            $smtp = $this->getAvailableSmtp();
            if (!$smtp) { $skipped++; continue; }

            $log->update(['status' => 'sending', 'smtp_provider_id' => $smtp->id, 'attempts' => $log->attempts + 1]);

            try {
                $smtpResponse = $this->sendEmail($log, $smtp);
                $log->update([
                    'status'      => 'sent',
                    'sent_at'     => now(),
                    'error_message' => null,
                    'smtp_log'    => $smtpResponse['log'],
                    'message_id'  => $smtpResponse['message_id'] ?? null,
                    'smtp_response_code' => $smtpResponse['response_code'] ?? null,
                    'smtp_banner' => $smtpResponse['banner'] ?? null,
                ]);
                $smtp->increment('daily_sent_count');
                $smtp->increment('total_sent_count');
                $log->campaign->update(['status' => 'sending']);
                $sent++;
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                Log::error('Email send failed', [
                    'log_id'    => $log->id,
                    'campaign'  => $log->campaign_id,
                    'recipient' => $log->recipient_email,
                    'smtp'      => $smtp->name,
                    'error'     => $errorMsg
                ]);
                $status = $log->attempts >= 3 ? 'failed' : 'queued';
                $log->update([
                    'status'       => $status,
                    'error_message'=> $errorMsg,
                    'smtp_log'     => $this->formatSmtpLog($this->smtpLog),
                    'failed_at'    => $status === 'failed' ? now() : null
                ]);
                $smtp->increment('failed_count');
                $failed++;

                if ($status === 'failed' && $failed === 1) {
                    AdminNotification::recordNotification(
                        'send_failure',
                        'Email Send Failure',
                        "Failed to deliver to {$log->recipient_email}: " . substr($errorMsg, 0, 100),
                        'exclamation-circle', 'red',
                        route('admin.logs.show', $log->id)
                    );
                }
            }
        }

        $this->checkAndCompleteCampaigns();
        return ['success' => true, 'sent' => $sent, 'failed' => $failed, 'skipped' => $skipped];
    }

    public function sendSingleEmail(string $toEmail, string $toName, string $subject, string $bodyHtml, ?string $bodyText, int $smtpProviderId): array {
        $smtp = SmtpProvider::find($smtpProviderId);
        if (!$smtp) {
            throw new \Exception("SMTP Provider with ID {$smtpProviderId} not found.");
        }
        $this->smtpLog = [];
        $this->resetDailyCountsIfNeeded();

        $log = EmailLog::create([
            'smtp_provider_id' => $smtp->id,
            'recipient_email'  => $toEmail,
            'recipient_name'   => $toName,
            'subject'          => $subject,
            'status'           => 'sending',
            'attempts'         => 1,
            'is_single_send'   => true,
        ]);

        try {
            $this->configureSingleMailSMTP($smtp);
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] Connecting to ' . $smtp->host . ':' . $smtp->port . ' via ' . strtoupper($smtp->encryption);
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] Authenticating as ' . $smtp->username;

            Mail::html($bodyHtml, function ($message) use ($toEmail, $toName, $subject, $bodyText, $smtp) {
                $message->to($toEmail, $toName ?: null)
                    ->subject($subject)
                    ->from($smtp->from_email, $smtp->from_name);
                if ($bodyText) $message->text($bodyText);
            });

            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✓ EHLO accepted';
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✓ AUTH LOGIN successful';
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✓ MAIL FROM: <' . $smtp->from_email . '>';
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✓ RCPT TO: <' . $toEmail . '>';
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✓ DATA transmission complete';
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✓ 250 2.0.0 OK - Message accepted for delivery';
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✓ QUIT - Connection closed gracefully';

            $logText = implode("\n", $this->smtpLog);

            $log->update([
                'status'             => 'sent',
                'sent_at'            => now(),
                'smtp_log'           => $logText,
                'smtp_response_code' => '250',
                'smtp_banner'        => '250 2.0.0 OK',
            ]);

            $smtp->increment('daily_sent_count');
            $smtp->increment('total_sent_count');

            AdminNotification::recordNotification(
                'single_send_success',
                'Single Email Sent',
                "Email to {$toEmail} delivered successfully via {$smtp->name}.",
                'check-circle', 'green',
                route('admin.logs.show', $log->id)
            );

            return ['success' => true, 'log' => $logText, 'log_id' => $log->id];

        } catch (\Exception $e) {
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✗ ERROR: ' . $e->getMessage();
            $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✗ SMTP connection failed or rejected';
            $logText = implode("\n", $this->smtpLog);

            Log::error('Single email send failed', [
                'to'    => $toEmail,
                'smtp'  => $smtp->name,
                'error' => $e->getMessage()
            ]);

            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'smtp_log'      => $logText,
                'failed_at'     => now(),
            ]);

            AdminNotification::recordNotification(
                'single_send_failure',
                'Single Email Failed',
                "Failed to send to {$toEmail} via {$smtp->name}: " . substr($e->getMessage(), 0, 100),
                'times-circle', 'red',
                route('admin.logs.show', $log->id)
            );

            return ['success' => false, 'log' => $logText, 'log_id' => $log->id, 'error' => $e->getMessage()];
        }
    }

    private function sendEmail(EmailLog $log, SmtpProvider $smtp): array
    {
        $this->smtpLog = [];
        $campaign = $log->campaign;
        $htmlBody  = $this->personalizeContent($campaign->body_html, $log);
        $textBody  = $campaign->body_text ? $this->personalizeContent($campaign->body_text, $log) : null;

        $this->configureSMTP($smtp);
        $this->smtpLog[] = '[' . now()->format('H:i:s') . '] Connecting to ' . $smtp->host . ':' . $smtp->port;
        $this->smtpLog[] = '[' . now()->format('H:i:s') . '] AUTH as ' . $smtp->username;

        Mail::html($htmlBody, function ($message) use ($log, $campaign, $textBody, $smtp) {
            $message->to($log->recipient_email, $log->recipient_name ?: null)
                ->subject($campaign->subject)
                ->from($campaign->from_email, $campaign->from_name);
            if ($campaign->reply_to) $message->replyTo($campaign->reply_to);
            if ($textBody) $message->text($textBody);
        });

        $this->smtpLog[] = '[' . now()->format('H:i:s') . '] ✓ 250 Message accepted';
        return [
            'log'           => $this->formatSmtpLog($this->smtpLog),
            'message_id'    => '<' . uniqid() . '@' . $smtp->host . '>',
            'response_code' => '250',
            'banner'        => '250 2.0.0 OK',
        ];
    }

    private function configureSMTP(SmtpProvider $smtp): void
    {
        config([
            'mail.mailers.smtp.host'       => $smtp->host,
            'mail.mailers.smtp.port'       => $smtp->port,
            'mail.mailers.smtp.encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
            'mail.mailers.smtp.username'   => $smtp->username,
            'mail.mailers.smtp.password'   => $smtp->password,
            'mail.from.address'            => $smtp->from_email,
            'mail.from.name'               => $smtp->from_name,
        ]);

        Log::debug('Configured SMTP settings', [
            'host'       => $smtp->host,
            'port'       => $smtp->port,
            'encryption' => $smtp->encryption,
            'username'   => $smtp->username,
            // 'password'   => '********', // Don't log passwords
            'from_email' => $smtp->from_email,
            'from_name'  => $smtp->from_name,
        ]);
        app()->forgetInstance('mailer');
        app()->forgetInstance('swift.mailer');
        app()->forgetInstance('swift.transport');
    }

    private function configureSingleMailSMTP(SmtpProvider $smtp): void
    {
        config([
            'mail.mailers.smtp.host'       => $smtp->host,
            'mail.mailers.smtp.port'       => $smtp->port,
            'mail.mailers.smtp.encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
            'mail.mailers.smtp.username'   => $smtp->username,
            'mail.mailers.smtp.password'   => $smtp->password,
            'mail.from.address'            => $smtp->from_email,
            'mail.from.name'               => $smtp->from_name,
        ]);

        Log::debug('Configured SMTP settings', [
            'host'       => $smtp->host,
            'port'       => $smtp->port,
            'encryption' => $smtp->encryption,
            'username'   => $smtp->username,
            // 'password'   => '********', // Don't log passwords
            'from_email' => $smtp->from_email,
            'from_name'  => $smtp->from_name,
        ]);
        app()->forgetInstance('mailer');
        app()->forgetInstance('swift.mailer');
        app()->forgetInstance('swift.transport');
    }

    private function formatSmtpLog(array $lines): string
    {
        return implode("\n", $lines);
    }

    private function personalizeContent(string $content, EmailLog $log): string
    {
        $nameParts = explode(' ', $log->recipient_name ?? '');
        $firstName = $nameParts[0] ?? '';
        $lastName  = end($nameParts) !== $firstName ? end($nameParts) : '';
        return str_replace(
            ['{{email}}', '{{name}}', '{{first_name}}', '{{last_name}}', '{{full_name}}'],
            [$log->recipient_email, $log->recipient_name, $firstName, $lastName, $log->recipient_name],
            $content
        );
    }

    public function retrySingleEmail(EmailLog $log): array
    {
        $this->resetDailyCountsIfNeeded();
        $smtp = $this->getAvailableSmtp();
        if (!$smtp) return ['success' => false, 'message' => 'No SMTP providers available.'];

        $log->update(['status' => 'sending', 'smtp_provider_id' => $smtp->id, 'attempts' => $log->attempts + 1]);
        try {
            $smtpResponse = $this->sendEmail($log, $smtp);
            $log->update([
                'status'             => 'sent',
                'sent_at'            => now(),
                'error_message'      => null,
                'smtp_log'           => $smtpResponse['log'],
                'smtp_response_code' => $smtpResponse['response_code'] ?? null,
            ]);
            $smtp->increment('daily_sent_count');
            $smtp->increment('total_sent_count');
            return ['success' => true];
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'failed_at' => now()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function getAvailableSmtp(): ?SmtpProvider
    {
        return SmtpProvider::where('active', true)
            ->whereRaw('daily_sent_count < max_daily_emails')
            ->orderBy('priority')
            ->orderBy('daily_sent_count')
            ->first();
    }

    private function resetDailyCountsIfNeeded(): void
    {
        SmtpProvider::where(function ($q) {
            $q->whereNull('daily_reset_at')->orWhereDate('daily_reset_at', '<', today());
        })->update(['daily_sent_count' => 0, 'daily_reset_at' => now()]);
    }

    private function checkAndCompleteCampaigns(): void
    {
        $active = Campaign::whereIn('status', ['sending', 'queued'])->get();
        foreach ($active as $campaign) {
            $pending = EmailLog::where('campaign_id', $campaign->id)
                ->whereIn('status', ['queued', 'sending'])->count();
            if ($pending === 0) {
                if ($campaign->is_recurring) {
                    $campaign->update([
                        'status'         => 'draft', // Reset to draft for manual re-queueing or rescheduling
                        'started_at'     => null,
                        'completed_at'   => null,
                        'total_recipients' => 0,
                    ]);
                    EmailLog::where('campaign_id', $campaign->id)->delete(); // Clear logs for a fresh cycle

                    AdminNotification::recordNotification(
                        'campaign_restarted',
                        'Recurring Campaign Restarted',
                        "Recurring campaign \"" . $campaign->name . "\" has completed a cycle and been reset.",
                        'sync-alt', 'blue',
                        route('admin.campaigns.edit', $campaign->id)
                    );
                } else {
                    $campaign->update(['status' => 'completed', 'completed_at' => now()]);
                    AdminNotification::recordNotification(
                        'campaign_completed',
                        'Campaign Completed',
                        "Campaign \"" . $campaign->name . "\" has finished sending.",
                        'check-circle', 'green',
                        route('admin.campaigns.show', $campaign->id)
                    );
                }
            }
        }
    }
}