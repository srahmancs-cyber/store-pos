<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'shop_name', 'value' => 'My Store', 'type' => 'string', 'group' => 'general'],
            ['key' => 'shop_address', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'shop_phone', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'tax_registration_number', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'string', 'group' => 'general'],
            // Tax
            ['key' => 'tax_name', 'value' => 'VAT', 'type' => 'string', 'group' => 'tax'],
            ['key' => 'tax_rate', 'value' => '0', 'type' => 'decimal', 'group' => 'tax'],
            ['key' => 'tax_inclusive', 'value' => '0', 'type' => 'boolean', 'group' => 'tax'],
            // Donation
            ['key' => 'donation_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'donation'],
            ['key' => 'donation_percentage', 'value' => '5', 'type' => 'decimal', 'group' => 'donation'],
            ['key' => 'donation_frequency', 'value' => 'monthly', 'type' => 'string', 'group' => 'donation'],
            // Owners
            ['key' => 'owner_a_name', 'value' => 'Owner A', 'type' => 'string', 'group' => 'owners'],
            ['key' => 'owner_b_name', 'value' => 'Owner B', 'type' => 'string', 'group' => 'owners'],
            ['key' => 'owner_a_share', 'value' => '50', 'type' => 'decimal', 'group' => 'owners'],
            ['key' => 'owner_b_share', 'value' => '50', 'type' => 'decimal', 'group' => 'owners'],
            // Receipt
            ['key' => 'receipt_header', 'value' => '', 'type' => 'string', 'group' => 'receipt'],
            ['key' => 'receipt_footer', 'value' => 'Thank you for shopping with us!', 'type' => 'string', 'group' => 'receipt'],
            // Payment methods
            ['key' => 'payment_methods', 'value' => json_encode(['cash', 'card']), 'type' => 'json', 'group' => 'payments'],        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
