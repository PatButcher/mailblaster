<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            AdminUserSeeder::class,
            SmtpProviderSeeder::class,
            ContactSeeder::class,
            CampaignSeeder::class,
        ]);
    }
}