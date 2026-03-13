<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class GenerateContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startNumber;
    protected $count;
    protected $digits;
    protected $domain;
    protected $tags;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($startNumber, $count, $digits, $domain, $tags)
    {
        $this->startNumber = $startNumber;
        $this->count = $count;
        $this->digits = $digits;
        $this->domain = $domain;
        $this->tags = $tags;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Starting GenerateContactsJob for {$this->count} contacts.");

        $generatedCount = 0;
        $skippedCount = 0;
        $errors = [];

        try {
            for ($i = 0; $i < $this->count; $i++) {
                $number = str_pad($this->startNumber + $i, $this->digits, '0', STR_PAD_LEFT);
                $email = strtolower($number . '@' . $this->domain);

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Invalid generated email: {$email}";
                    $skippedCount++;
                    continue;
                }

                // Check for existing contact to prevent duplicates
                if (Contact::where('email', $email)->exists()) {
                    $skippedCount++;
                    continue;
                }

                Contact::create([
                    'email' => $email,
                    'tags' => $this->tags,
                    'subscribed' => true,
                    'source' => 'email_generator'
                ]);
                $generatedCount++;
            }

            $message = "GenerateContactsJob complete: {$generatedCount} contacts added, {$skippedCount} skipped (duplicates or invalid format).";
            Log::info($message);
            // Optionally, you might want to send a notification to the admin user here.

        } catch (\Exception $e) {
            Log::error("GenerateContactsJob failed: " . $e->getMessage(), [
                'startNumber' => $this->startNumber,
                'count' => $this->count,
                'domain' => $this->domain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Optionally, notify admin of job failure.
        }
    }
}
