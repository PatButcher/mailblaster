<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmtpProvider;

class SmtpProviderSeeder extends Seeder
{
    public function run()
    {
        $providers = [
            [
                'name' => 'SendGrid Primary',
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'username' => 'apikey',
                'password' => 'SG.your-sendgrid-api-key-here',
                'encryption' => 'tls',
                'from_email' => 'noreply@yourcompany.com',
                'from_name' => 'Your Company',
                'max_daily_emails' => 1000,
                'priority' => 1,
                'active' => true,
            ],
            [
                'name' => 'Mailgun Backup',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'username' => 'postmaster@mg.yourcompany.com',
                'password' => 'your-mailgun-password-here',
                'encryption' => 'tls',
                'from_email' => 'noreply@yourcompany.com',
                'from_name' => 'Your Company',
                'max_daily_emails' => 800,
                'priority' => 2,
                'active' => true,
            ],
            [
                'name' => 'Amazon SES',
                'host' => 'email-smtp.us-east-1.amazonaws.com',
                'port' => 587,
                'username' => 'AKIAIOSFODNN7EXAMPLE',
                'password' => 'your-ses-smtp-secret-key-here',
                'encryption' => 'tls',
                'from_email' => 'campaigns@yourcompany.com',
                'from_name' => 'Your Company Marketing',
                'max_daily_emails' => 5000,
                'priority' => 3,
                'active' => false,
            ],
            [
                'name' => 'SMTP2GO',
                'host' => 'mail.smtp2go.com',
                'port' => 587,
                'username' => 'your-smtp2go-username',
                'password' => 'your-smtp2go-password',
                'encryption' => 'tls',
                'from_email' => 'info@yourcompany.com',
                'from_name' => 'Your Company Info',
                'max_daily_emails' => 1000,
                'priority' => 4,
                'active' => false,
            ],
            [
                'name' => 'Postmark Transactional',
                'host' => 'smtp.postmarkapp.com',
                'port' => 587,
                'username' => 'your-postmark-api-token',
                'password' => 'your-postmark-api-token',
                'encryption' => 'tls',
                'from_email' => 'hello@yourcompany.com',
                'from_name' => 'Your Company Hello',
                'max_daily_emails' => 2000,
                'priority' => 5,
                'active' => false,
            ],
        ];

        foreach ($providers as $provider) {
            SmtpProvider::create($provider);
        }
    }
}