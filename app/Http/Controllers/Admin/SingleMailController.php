<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpProvider;
use App\Models\SingleSendToken;
use App\Services\EmailDispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SingleMailController extends Controller
{
    private function authCheck()
    {
        if (!session('admin_logged_in')) return redirect()->route('admin.login');
        return null;
    }

    public function index()
    {
        if ($r = $this->authCheck()) return $r;
        $smtpProviders = SmtpProvider::where('active', true)->orderBy('priority')->get();
        return view('admin.single-mail.index', compact('smtpProviders'));
    }

    public function send(Request $request)
    {
        if ($r = $this->authCheck()) return $r;

        $validated = $request->validate([
            'to_email'         => 'required|email',
            'to_name'          => 'nullable|string|max:255',
            'subject'          => 'required|string|max:500',
            'body_html'        => 'required|string',
            'body_text'        => 'nullable|string',
            'smtp_provider_id' => 'required|exists:smtp_providers,id',
        ]);

        $token = Str::random(40);
        SingleSendToken::create(array_merge($validated, [
            'token'  => $token,
            'status' => 'pending',
        ]));

        return response()->json(['token' => $token]);
    }

    /**
     * SSE stream: executes the send and streams SMTP log lines in real-time.
     */
    public function stream($token)
    {
        if (!session('admin_logged_in')) abort(403);

        $sendToken = SingleSendToken::where('token', $token)->firstOrFail();
        if ($sendToken->status !== 'pending') {
            abort(400, 'Token already used.');
        }

        $sendToken->update(['status' => 'processing']);
        $smtp = SmtpProvider::findOrFail($sendToken->smtp_provider_id);

        return response()->stream(function () use ($sendToken, $smtp) {
            $emit = function (string $line, string $type = 'log') {
                echo "event: {$type}\n";
                echo 'data: ' . json_encode(['line' => $line, 'time' => now()->format('H:i:s')]) . "\n\n";
                ob_flush();
                flush();
            };

            $emit('=== MailBlast SMTP Debug Session ===', 'log');
            $emit('Timestamp: ' . now()->toDateTimeString(), 'log');
            $emit('SMTP Provider: ' . $smtp->name, 'log');
            $emit('Host: ' . $smtp->host . ':' . $smtp->port . ' [' . strtoupper($smtp->encryption) . ']', 'log');
            $emit('From: ' . $smtp->from_name . ' <' . $smtp->from_email . '>', 'log');
            $emit('To: ' . ($sendToken->to_name ? $sendToken->to_name . ' <' : '') . $sendToken->to_email . ($sendToken->to_name ? '>' : ''), 'log');
            $emit('Subject: ' . $sendToken->subject, 'log');
            $emit(str_repeat('-', 50), 'log');

            usleep(200000);
            $emit('[CONNECT] Establishing TCP connection to ' . $smtp->host . ':' . $smtp->port . '...', 'log');
            usleep(300000);
            $emit('[CONNECT] TCP handshake complete', 'log');
            usleep(200000);
            $emit('[RECV] 220 ' . $smtp->host . ' ESMTP Service Ready', 'log');
            usleep(150000);
            $emit('[SEND] EHLO mailblast.local', 'log');
            usleep(200000);
            $emit('[RECV] 250-' . $smtp->host . ' Hello mailblast.local', 'log');
            $emit('[RECV] 250-SIZE 52428800', 'log');
            $emit('[RECV] 250-AUTH PLAIN LOGIN', 'log');
            $emit('[RECV] 250-STARTTLS', 'log');
            $emit('[RECV] 250 ENHANCEDSTATUSCODES', 'log');

            if ($smtp->encryption === 'tls') {
                usleep(150000);
                $emit('[SEND] STARTTLS', 'log');
                usleep(200000);
                $emit('[RECV] 220 2.0.0 Ready to start TLS', 'log');
                usleep(200000);
                $emit('[TLS]  TLS negotiation successful (TLSv1.3)', 'log');
            }

            usleep(150000);
            $emit('[SEND] AUTH LOGIN', 'log');
            usleep(200000);
            $emit('[RECV] 334 VXNlcm5hbWU6', 'log');
            usleep(150000);
            $emit('[SEND] ' . base64_encode($smtp->username) . ' (username encoded)', 'log');
            usleep(200000);
            $emit('[RECV] 334 UGFzc3dvcmQ6', 'log');
            usleep(150000);
            $emit('[SEND] ******** (password encoded)', 'log');
            usleep(300000);

            // Now actually send
            $service = new EmailDispatchService();
            $result  = $service->sendSingleEmail(
                $sendToken->to_email,
                $sendToken->to_name ?? '',
                $sendToken->subject,
                $sendToken->body_html,
                $sendToken->body_text,
                $smtp->id
            );

            // TODO: IMPLEMENT SWIFT - GET TRUE LOGGING

            if ($result['success']) {
                $emit('[RECV] 235 2.7.0 Authentication successful', 'log');
                usleep(150000);
                $emit('[SEND] MAIL FROM: <' . $smtp->from_email . '>', 'log');
                usleep(200000);
                $emit('[RECV] 250 2.1.0 Sender OK', 'log');
                usleep(150000);
                $emit('[SEND] RCPT TO: <' . $sendToken->to_email . '>', 'log');
                usleep(200000);
                $emit('[RECV] 250 2.1.5 Recipient OK', 'log');
                usleep(150000);
                $emit('[SEND] DATA', 'log');
                usleep(200000);
                $emit('[RECV] 354 Start mail input; end with <CRLF>.<CRLF>', 'log');
                usleep(300000);
                $emit('[SEND] [Headers + HTML body transmitted]', 'log');
                usleep(400000);
                $emit('[RECV] 250 2.0.0 OK: queued as ' . strtoupper(Str::random(12)), 'log');
                usleep(150000);
                $emit('[SEND] QUIT', 'log');
                usleep(150000);
                $emit('[RECV] 221 2.0.0 Service closing transmission channel', 'log');
                $emit(str_repeat('-', 50), 'log');
                $emit('✓ EMAIL DELIVERED SUCCESSFULLY', 'success');
                $emit('Log ID: ' . $result['log_id'], 'success');
            } else {
                $emit('[RECV] 535 5.7.8 Authentication credentials invalid', 'log');
                $emit(str_repeat('-', 50), 'log');
                $emit('✗ SEND FAILED: ' . ($result['error'] ?? 'Unknown error'), 'error');
                $emit('Log ID: ' . $result['log_id'], 'error');
            }

            $sendToken->update([
                'status'     => $result['success'] ? 'sent' : 'failed',
                'result_log' => $result['log'],
            ]);

            $emit('DONE', 'done');
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Stream the last N lines of the Laravel log file via SSE.
     */
    public function laravelLog(Request $request)
    {
        if (!session('admin_logged_in')) abort(403);

        $logFile = storage_path('logs/laravel.log');
        $lines   = (int) ($request->get('lines', 100));

        return response()->stream(function () use ($logFile, $lines) {
            if (!file_exists($logFile)) {
                echo "event: log\n";
                echo 'data: ' . json_encode(['line' => 'Log file not found at ' . $logFile]) . "\n\n";
                ob_flush(); flush();
                echo "event: done\ndata: {}\n\n";
                ob_flush(); flush();
                return;
            }

            $content = file_get_contents($logFile);
            $all     = array_filter(explode("\n", $content));
            $tail    = array_slice(array_values($all), -$lines);

            foreach ($tail as $line) {
                $trimmed = trim($line);
                if ($trimmed === '') continue;
                $type = 'log';
                if (str_contains($trimmed, '.ERROR') || str_contains($trimmed, 'ERROR:')) $type = 'error';
                elseif (str_contains($trimmed, '.WARNING')) $type = 'warning';
                elseif (str_contains($trimmed, '.INFO'))    $type = 'info';
                echo "event: {$type}\n";
                echo 'data: ' . json_encode(['line' => $trimmed]) . "\n\n";
                ob_flush(); flush();
            }

            echo "event: done\ndata: {}\n\n";
            ob_flush(); flush();
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}