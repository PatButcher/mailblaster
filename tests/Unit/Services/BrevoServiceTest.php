<?php

namespace Tests\Unit\Services;

use App\Services\BrevoService;
use Brevo\Brevo;
use Brevo\Client\Api\CampaignsApi;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Model\GetAccount200Response;
use Brevo\Client\Model\GetCampaignStats;
use Brevo\Client\Model\GetContacts200Response;
use Brevo\Client\Model\GetContacts200ResponseContactsInner;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class BrevoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure Brevo API key is cleared for isolated tests
        Config::set('services.brevo.key', null);
        // Reset Mockery mocks before each test
        Mockery::close();
    }

    public function test_brevo_service_is_configured_with_api_key(): void
    {
        Config::set('services.brevo.key', 'test_api_key');

        $mockBrevo = Mockery::mock(Brevo::class);
        $mockBrevo->shouldReceive('account')->andReturn(
            Mockery::mock(GetAccount200Response::class) // Not strictly needed, but to satisfy constructor init
        )->zeroOrMoreTimes();

        // This allows us to test the constructor behavior without actually instantiating Brevo directly
        // We'll replace the Brevo instance with our mock for other methods
        $service = new BrevoService();

        $this->assertTrue($service->isConfigured());
    }

    public function test_brevo_service_is_not_configured_without_api_key(): void
    {
        // API key is null by default in setUp
        $service = new BrevoService();

        $this->assertFalse($service->isConfigured());
        Log::shouldReceive('warning')
            ->with('Brevo API key not configured. Brevo services will be unavailable.')
            ->once();
    }

    public function test_get_campaign_statistics_returns_data_on_success(): void
    {
        Config::set('services.brevo.key', 'test_api_key');

        $mockCampaignsApi = Mockery::mock(CampaignsApi::class);
        $mockCampaignsApi->shouldReceive('getCampaignStats')
            ->with(123)
            ->once()
            ->andReturn(
                (new GetCampaignStats())->setCampaignId(123)->setSent(100)->setDelivered(90)
            );

        $mockBrevo = Mockery::mock(Brevo::class);
        $mockBrevo->shouldReceive('campaigns')->andReturn($mockCampaignsApi);
        // This is a bit tricky to mock a dependency injected in constructor without modifying the service.
        // For actual tests, we would inject the mocked Brevo instance.
        // For now, we'll bypass the constructor's Brevo instantiation for this test,
        // and assume a valid Brevo instance would be available if `isConfigured` is true.

        $service = new BrevoService();
        $service->brevo = $mockBrevo; // Inject mock for testing purposes

        $stats = $service->getCampaignStatistics(123);

        $this->assertIsArray($stats);
        $this->assertEquals(123, $stats['campaignId']);
        $this->assertEquals(90, $stats['delivered']);
    }

    public function test_get_campaign_statistics_returns_null_on_api_exception(): void
    {
        Config::set('services.brevo.key', 'test_api_key');

        $mockCampaignsApi = Mockery::mock(CampaignsApi::class);
        $mockCampaignsApi->shouldReceive('getCampaignStats')
            ->with(123)
            ->once()
            ->andThrow(new Exception('API error'));

        $mockBrevo = Mockery::mock(Brevo::class);
        $mockBrevo->shouldReceive('campaigns')->andReturn($mockCampaignsApi);

        Log::shouldReceive('error')
            ->with('Failed to fetch campaign statistics for campaign ID 123: API error')
            ->once();

        $service = new BrevoService();
        $service->brevo = $mockBrevo; // Inject mock for testing purposes

        $stats = $service->getCampaignStatistics(123);

        $this->assertNull($stats);
    }

    public function test_get_campaign_statistics_returns_null_when_not_configured(): void
    {
        // API key is null by default in setUp
        $service = new BrevoService();

        Log::shouldReceive('warning')
            ->with('Brevo service not configured. Cannot fetch campaign statistics for campaign ID 123.')
            ->once();

        $stats = $service->getCampaignStatistics(123);

        $this->assertNull($stats);
    }

    public function test_get_unsubscribed_contacts_returns_emails_on_success(): void
    {
        Config::set('services.brevo.key', 'test_api_key');

        $mockContactsApi = Mockery::mock(ContactsApi::class);
        $mockContactsApi->shouldReceive('getContacts')
            ->withArgs(function ($limit, $offset, $query) {
                // Assert the 'query' parameter is passed as expected
                return $limit === 100 && $offset === 0 && $query === 'inBlacklist:true';
            })
            ->once()
            ->andReturn(
                (new GetContacts200Response())->setContacts([
                    (new GetContacts200ResponseContactsInner())->setEmail('unsub1@example.com'),
                    (new GetContacts200ResponseContactsInner())->setEmail('unsub2@example.com'),
                ])
            );

        $mockBrevo = Mockery::mock(Brevo::class);
        $mockBrevo->shouldReceive('contacts')->andReturn($mockContactsApi);
        Log::shouldReceive('warning')
            ->with("Brevo SDK's getContacts method may not directly support 'inBlacklist:true' query. Please refer to Brevo API documentation for fetching unsubscribed contacts effectively with the PHP SDK.")
            ->once();


        $service = new BrevoService();
        $service->brevo = $mockBrevo; // Inject mock for testing purposes

        $emails = $service->getUnsubscribedContacts();

        $this->assertIsArray($emails);
        $this->assertCount(2, $emails);
        $this->assertEquals(['unsub1@example.com', 'unsub2@example.com'], $emails);
    }

    public function test_get_unsubscribed_contacts_returns_empty_array_on_api_exception(): void
    {
        Config::set('services.brevo.key', 'test_api_key');

        $mockContactsApi = Mockery::mock(ContactsApi::class);
        $mockContactsApi->shouldReceive('getContacts')
            ->once()
            ->andThrow(new Exception('API error'));

        $mockBrevo = Mockery::mock(Brevo::class);
        $mockBrevo->shouldReceive('contacts')->andReturn($mockContactsApi);

        Log::shouldReceive('warning')
            ->with("Brevo SDK's getContacts method may not directly support 'inBlacklist:true' query. Please refer to Brevo API documentation for fetching unsubscribed contacts effectively with the PHP SDK.")
            ->once();
        Log::shouldReceive('error')
            ->with('Failed to fetch unsubscribed contacts from Brevo: API error')
            ->once();

        $service = new BrevoService();
        $service->brevo = $mockBrevo; // Inject mock for testing purposes

        $emails = $service->getUnsubscribedContacts();

        $this->assertIsArray($emails);
        $this->assertEmpty($emails);
    }

    public function test_get_unsubscribed_contacts_returns_empty_array_when_not_configured(): void
    {
        // API key is null by default in setUp
        $service = new BrevoService();

        Log::shouldReceive('warning')
            ->with('Brevo service not configured. Cannot fetch unsubscribed contacts.')
            ->once();

        $emails = $service->getUnsubscribedContacts();

        $this->assertIsArray($emails);
        $this->assertEmpty($emails);
    }
}
