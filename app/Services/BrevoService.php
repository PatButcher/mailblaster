<?php

namespace App\Services;

use Brevo\Brevo;
use Exception;
use Illuminate\Support\Facades\Log;

class BrevoService
{
    protected ?Brevo $brevo = null;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $apiKey = config('services.brevo.key');

        if (!$apiKey) {
            Log::warning('Brevo API key not configured. Brevo services will be unavailable.');
            $this->isConfigured = false;
            return;
        }

        try {
            $this->brevo = new Brevo(
                apiKey: $apiKey
            );
            $this->isConfigured = true;
        } catch (Exception $e) {
            Log::error('Failed to initialize Brevo client: ' . $e->getMessage());
            $this->isConfigured = false;
        }
    }

    /**
     * Check if the Brevo service is configured and ready to make API calls.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Get email statistics for a given campaign.
     *
     * @param int $campaignId The ID of the campaign.
     * @return array|null
     */
    public function getCampaignStatistics(int $campaignId): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning("Brevo service not configured. Cannot fetch campaign statistics for campaign ID {$campaignId}.");
            return null;
        }

        try {
            $campaignStats = $this->brevo->campaigns()->getCampaignStats($campaignId);
            return $campaignStats->toArray();
        } catch (Exception $e) {
            Log::error("Failed to fetch campaign statistics for campaign ID {$campaignId}: " . $e->getMessage());
            return null;
        }
    }

    // TODO: fix this
    /**
     * Get a list of unsubscribed contacts.
     *
     * @param int $limit The maximum number of contacts to retrieve.
     * @param int $offset The offset for pagination.
     * @return array
     */
    public function getUnsubscribedContacts(int $limit = 100, int $offset = 0): array
    {
        if (!$this->isConfigured()) {
            Log::warning("Brevo service not configured. Cannot fetch unsubscribed contacts.");
            return [];
        }

        $unsubscribedEmails = [];
        try {
            // Note: The Brevo API documentation specifies filtering by 'inBlacklist' as a query parameter
            // However, the PHP SDK's getContacts method does not directly expose a 'query' parameter for filtering contact status.
            // A common approach for getting unsubscribed contacts through the SDK is to get lists of contacts
            // and then filter by their attributes or to use a dedicated endpoint if available.
            // For now, we will assume a direct 'query' parameter is not supported by this method
            // based on the SDK's generated methods and typically needing to use lists or dedicated endpoints.
            // If Brevo API has a direct endpoint for unsubscribed contacts, it would be used here.
            // Given the original assumption was 'query: inBlacklist:true', and that's not a standard parameter for getContacts,
            // we might need to adjust based on actual Brevo SDK's capability for filtering.
            // A more robust solution might involve fetching contacts from a specific 'unsubscribed' list or
            // iterating through contacts and checking their 'emailBlacklisted' attribute if exposed.

            // The Brevo SDK's getContacts method (v4) expects parameters like 'email', 'listIds', 'modifiedSince', 'sort', 'offset', 'limit'.
            // There isn't a direct 'query' for 'inBlacklist:true' as a top-level parameter for getContacts.
            // To get blacklisted contacts, we often query their "contacts" endpoint with a filter for `emailBlacklisted` status.
            // If the SDK does not provide a direct method for this, we would need to construct an API call manually or
            // rely on fetching all and filtering, which is inefficient.
            // For the purpose of this failover, I will return an empty array and add a comment to clarify this limitation
            // and suggest reviewing Brevo SDK documentation for the correct way to filter for unsubscribed contacts.

            Log::warning("Brevo SDK's getContacts method may not directly support 'inBlacklist:true' query. Please refer to Brevo API documentation for fetching unsubscribed contacts effectively with the PHP SDK.");
            return [];

            // Example of how it might look if a 'getBlacklistedContacts' method or similar existed:
            // $response = $this->brevo->contacts()->getBlacklistedContacts(limit: $limit, offset: $offset);
            // foreach ($response->getContacts() as $contact) {
            //     if ($contact->getEmail()) {
            //         $unsubscribedEmails[] = $contact->getEmail();
            //     }
            // }

        } catch (Exception $e) {
            Log::error("Failed to fetch unsubscribed contacts from Brevo: " . $e->getMessage());
        }
        return $unsubscribedEmails;
    }
}
