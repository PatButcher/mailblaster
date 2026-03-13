<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\QueueRecurringCampaigns;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('campaigns:queue-recurring', function () {
    $this->call(QueueRecurringCampaigns::class);
})->purpose('Finds recurring campaigns in "draft" status and adds them to the email queue.');
