<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRecipientBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Campaign $campaign;
    public array $recipients;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120; // 2 minutes, should be enough for a batch

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Campaign $campaign, array $recipients)
    {
        $this->campaign = $campaign;
        $this->recipients = $recipients;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $existingEmails = EmailLog::where('campaign_id', $this->campaign->id)
            ->whereIn('status', ['queued', 'sent', 'sending'])
            ->pluck('recipient_email')->toArray();

        foreach ($this->recipients as $recipient) {
            if (in_array($recipient['email'], $existingEmails)) {
                continue;
            }

            EmailLog::create([
                'campaign_id'    => $this->campaign->id,
                'recipient_email'=> $recipient['email'],
                'recipient_name' => $recipient['name'],
                'subject'        => $this->campaign->subject,
                'status'         => 'queued',
                'attempts'       => 0,
                'is_single_send' => false,
            ]);
        }
    }
}
