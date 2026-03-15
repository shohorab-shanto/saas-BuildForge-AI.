<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        // 1. Create Admins for each organization
        foreach ($organizations as $org) {
            $admin = User::create([
                'name' => 'Admin - ' . $org->name,
                'email' => 'admin@' . $org->slug . '.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('Admin');
            
            // 2. Create Developers for each organization
            for ($i = 0; $i < 2; $i++) {
                $dev = User::create([
                    'name' => 'Dev ' . ($i+1) . ' - ' . $org->name,
                    'email' => 'dev' . ($i+1) . '@' . $org->slug . '.com',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
                $dev->assignRole('Developer');
            }

            // 3. Create Viewers for each organization
            $viewer = User::create([
                'name' => 'Viewer - ' . $org->name,
                'email' => 'viewer@' . $org->slug . '.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $viewer->assignRole('Viewer');
        }

        // 4. Create some random users not tied to specific roles yet
        User::factory(10)->create();
    }
}
