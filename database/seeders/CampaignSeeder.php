<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;

class CampaignSeeder extends Seeder
{
    public function run()
    {
        $campaigns = [
            [
                'name' => 'Q1 Newsletter - Product Updates',
                'subject' => 'Exciting Product Updates for Q1 2024',
                'from_name' => 'Your Company Team',
                'from_email' => 'newsletter@yourcompany.com',
                'body_html' => '<h1>Hello {{first_name}}!</h1><p>We have exciting updates to share with you this quarter. Our team has been working hard to bring you the best features and improvements.</p><p>Check out what\'s new:</p><ul><li>Feature 1: Enhanced dashboard</li><li>Feature 2: Improved performance</li><li>Feature 3: New integrations</li></ul><p>Best regards,<br>Your Company Team</p>',
                'body_text' => 'Hello {{first_name}}! We have exciting updates for Q1 2024.',
                'recipient_filter' => 'subscribed',
                'status' => 'completed',
                'total_recipients' => 18,
                'batch_size' => 50,
                'delay_between_batches' => 60,
                'created_by' => 'admin',
                'started_at' => now()->subDays(5),
                'completed_at' => now()->subDays(5),
            ],
            [
                'name' => 'VIP Customer Appreciation Campaign',
                'subject' => 'Thank You for Being a VIP Customer, {{first_name}}!',
                'from_name' => 'Your Company',
                'from_email' => 'vip@yourcompany.com',
                'body_html' => '<h1>Dear {{first_name}},</h1><p>As one of our most valued customers, we want to express our sincere gratitude for your continued support.</p><p>As a token of our appreciation, we\'re offering you an exclusive 20% discount on your next purchase.</p><p>Use code: VIP20</p><p>Warmly,<br>The VIP Team</p>',
                'recipient_filter' => 'tagged',
                'tags_filter' => 'vip',
                'status' => 'draft',
                'total_recipients' => 0,
                'batch_size' => 25,
                'delay_between_batches' => 30,
                'created_by' => 'admin',
            ],
            [
                'name' => 'Enterprise Solutions Outreach',
                'subject' => 'Scalable Enterprise Solutions for {{company}}',
                'from_name' => 'Enterprise Sales Team',
                'from_email' => 'enterprise@yourcompany.com',
                'body_html' => '<h1>Hello {{first_name}},</h1><p>We noticed that {{company}} could benefit from our enterprise-grade solutions. Our platform helps businesses like yours scale efficiently.</p><p>Would you be open to a quick 15-minute demo this week?</p><p>Best,<br>Enterprise Team</p>',
                'recipient_filter' => 'tagged',
                'tags_filter' => 'enterprise',
                'status' => 'paused',
                'total_recipients' => 4,
                'batch_size' => 10,
                'delay_between_batches' => 120,
                'created_by' => 'manager',
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::create($campaign);
        }
    }
}