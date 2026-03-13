<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use App\Services\EmailDispatchService;

class QueueRecurringCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:queue-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds recurring campaigns in "draft" status and adds them to the email queue.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for recurring campaigns to queue...');

        $campaigns = Campaign::where('status', 'draft')
                             ->where('is_recurring', true)
                             ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No recurring campaigns in "draft" status found.');
            return Command::SUCCESS;
        }

        $emailDispatchService = new EmailDispatchService();
        $queuedCount = 0;

        foreach ($campaigns as $campaign) {
            $result = $emailDispatchService->queueCampaign($campaign);
            if ($result['success']) {
                $campaign->update(['status' => 'queued', 'started_at' => now()]);
                $this->info("Campaign '{$campaign->name}' (ID: {$campaign->id}) queued successfully. {$result['queued']} emails added.");
                $queuedCount++;
            } else {
                $this->error("Failed to queue campaign '{$campaign->name}' (ID: {$campaign->id}): {$result['message']}");
            }
        }

        $this->info("Finished queuing recurring campaigns. Total queued: {$queuedCount}");

        return Command::SUCCESS;
    }
}
