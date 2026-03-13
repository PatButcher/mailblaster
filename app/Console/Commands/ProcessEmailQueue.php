<?php

namespace App\Console\Commands;

use App\Services\EmailDispatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes the email queue to send out queued emails.';

    /**
     * The EmailDispatchService instance.
     *
     * @var \App\Services\EmailDispatchService
     */
    protected $emailDispatchService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\EmailDispatchService $emailDispatchService
     * @return void
     */
    public function __construct(EmailDispatchService $emailDispatchService)
    {
        parent::__construct();
        $this->emailDispatchService = $emailDispatchService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Starting email queue processing...');
        try {
            $result = $this->emailDispatchService->processQueue();
            $this->info("Email queue processed. Sent: {$result['sent']}, Failed: {$result['failed']}, Skipped: {$result['skipped']}");
            Log::info('Email queue processing completed.', $result);
            return 0; // Command executed successfully
        } catch (\Exception $e) {
            $this->error("Error processing email queue: {$e->getMessage()}");
            Log::error('Error processing email queue.', ['error' => $e->getMessage()]);
            return 1; // Command failed
        }
    }
}