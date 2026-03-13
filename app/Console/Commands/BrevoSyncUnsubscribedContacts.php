<?php

namespace App\Console\Commands;

use App\Models\BlockedContact;
use App\Models\Contact;
use App\Services\BrevoService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BrevoSyncUnsubscribedContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brevo:sync-unsubscribed-contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches unsubscribed contacts from Brevo, marks them in the local contacts table, and logs them in the blocked_contacts table.';

    protected BrevoService $brevoService;

    public function __construct(BrevoService $brevoService)
    {
        parent::__construct();
        $this->brevoService = $brevoService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Brevo unsubscribed contacts synchronization...');

        try {
            $unsubscribedEmails = $this->brevoService->getUnsubscribedContacts();

            if (empty($unsubscribedEmails)) {
                $this->info('No unsubscribed contacts found in Brevo.');
                return Command::SUCCESS;
            }

            $this->info(sprintf('Found %d unsubscribed contacts in Brevo. Processing...', count($unsubscribedEmails)));

            $syncedCount = 0;
            foreach ($unsubscribedEmails as $email) {
                // Mark contact as unsubscribed in the local contacts table
                $contact = Contact::where('email', $email)->first();
                if ($contact && $contact->subscribed) {
                    $contact->update([
                        'subscribed' => false,
                        'unsubscribed_at' => Carbon::now(),
                    ]);
                    $this->info("Contact '{$email}' marked as unsubscribed in local contacts table.");
                }

                // Log the unsubscribed contact in the blocked_contacts table
                if (!BlockedContact::where('email', $email)->exists()) {
                    BlockedContact::create([
                        'email' => $email,
                        'reason' => 'Unsubscribed via Brevo',
                        'blocked_at' => Carbon::now(),
                    ]);
                    $this->info("Contact '{$email}' added to blocked_contacts table.");
                }
                $syncedCount++;
            }

            $this->info("Successfully synchronized {$syncedCount} unsubscribed contacts.");
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('An error occurred during synchronization: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
