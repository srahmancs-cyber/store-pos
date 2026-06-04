<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SettingsSeeder::class);

        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@store.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create two owners
        Owner::firstOrCreate(['sort_order' => 1], ['name' => 'Owner A', 'profit_share_percentage' => 50.00, 'is_active' => true]);
        Owner::firstOrCreate(['sort_order' => 2], ['name' => 'Owner B', 'profit_share_percentage' => 50.00, 'is_active' => true]);
    }
}
