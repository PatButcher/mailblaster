<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactSeeder extends Seeder
{
    public function run()
    {
        $contacts = [
            ['email' => 'alice.johnson@techcorp.com', 'first_name' => 'Alice', 'last_name' => 'Johnson', 'company' => 'TechCorp Inc', 'tags' => 'enterprise,vip', 'subscribed' => true],
            ['email' => 'bob.smith@designstudio.io', 'first_name' => 'Bob', 'last_name' => 'Smith', 'company' => 'Design Studio', 'tags' => 'agency,design', 'subscribed' => true],
            ['email' => 'carol.white@retailplus.com', 'first_name' => 'Carol', 'last_name' => 'White', 'company' => 'RetailPlus', 'tags' => 'retail,ecommerce', 'subscribed' => true],
            ['email' => 'david.brown@fintech.net', 'first_name' => 'David', 'last_name' => 'Brown', 'company' => 'FinTech Solutions', 'tags' => 'finance,enterprise', 'subscribed' => true],
            ['email' => 'emma.davis@healthcare.org', 'first_name' => 'Emma', 'last_name' => 'Davis', 'company' => 'HealthCare Plus', 'tags' => 'healthcare', 'subscribed' => true],
            ['email' => 'frank.miller@startup.co', 'first_name' => 'Frank', 'last_name' => 'Miller', 'company' => 'StartUp Co', 'tags' => 'startup,tech', 'subscribed' => true],
            ['email' => 'grace.wilson@marketing.agency', 'first_name' => 'Grace', 'last_name' => 'Wilson', 'company' => 'Marketing Agency', 'tags' => 'agency,marketing', 'subscribed' => true],
            ['email' => 'henry.moore@logistics.com', 'first_name' => 'Henry', 'last_name' => 'Moore', 'company' => 'Logistics Pro', 'tags' => 'logistics', 'subscribed' => true],
            ['email' => 'isabel.taylor@edutech.edu', 'first_name' => 'Isabel', 'last_name' => 'Taylor', 'company' => 'EduTech Academy', 'tags' => 'education', 'subscribed' => true],
            ['email' => 'james.anderson@realestate.com', 'first_name' => 'James', 'last_name' => 'Anderson', 'company' => 'Real Estate Group', 'tags' => 'realestate,vip', 'subscribed' => true],
            ['email' => 'karen.thomas@consulting.biz', 'first_name' => 'Karen', 'last_name' => 'Thomas', 'company' => 'Consulting Biz', 'tags' => 'consulting', 'subscribed' => true],
            ['email' => 'liam.jackson@software.dev', 'first_name' => 'Liam', 'last_name' => 'Jackson', 'company' => 'Software Dev Co', 'tags' => 'tech,developer', 'subscribed' => true],
            ['email' => 'mia.harris@media.com', 'first_name' => 'Mia', 'last_name' => 'Harris', 'company' => 'Media House', 'tags' => 'media,agency', 'subscribed' => true],
            ['email' => 'noah.martin@manufacturing.net', 'first_name' => 'Noah', 'last_name' => 'Martin', 'company' => 'Manufacturing Corp', 'tags' => 'manufacturing', 'subscribed' => false],
            ['email' => 'olivia.garcia@foodbrand.com', 'first_name' => 'Olivia', 'last_name' => 'Garcia', 'company' => 'Food Brand Inc', 'tags' => 'food,retail', 'subscribed' => true],
            ['email' => 'peter.martinez@legal.law', 'first_name' => 'Peter', 'last_name' => 'Martinez', 'company' => 'Legal Partners', 'tags' => 'legal,professional', 'subscribed' => true],
            ['email' => 'quinn.robinson@travel.agency', 'first_name' => 'Quinn', 'last_name' => 'Robinson', 'company' => 'Travel Agency Plus', 'tags' => 'travel,leisure', 'subscribed' => true],
            ['email' => 'rachel.clark@nonprofit.org', 'first_name' => 'Rachel', 'last_name' => 'Clark', 'company' => 'Nonprofit Foundation', 'tags' => 'nonprofit', 'subscribed' => true],
            ['email' => 'samuel.rodriguez@auto.com', 'first_name' => 'Samuel', 'last_name' => 'Rodriguez', 'company' => 'Auto Dealership', 'tags' => 'automotive', 'subscribed' => false],
            ['email' => 'tina.lewis@beauty.brand', 'first_name' => 'Tina', 'last_name' => 'Lewis', 'company' => 'Beauty Brand Co', 'tags' => 'beauty,retail,vip', 'subscribed' => true],
        ];

        foreach ($contacts as $contact) {
            Contact::create(array_merge($contact, ['source' => 'seeder']));
        }
    }
}