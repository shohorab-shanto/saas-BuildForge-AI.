<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class OrganizationsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a Primary Organization for testing
        $ownerUser = User::create([
            'name' => 'John Doe',
            'email' => 'john@buildforge.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $ownerUser->assignRole('Owner');

        Organization::create([
            'name' => 'BuildForge AI Main',
            'slug' => 'buildforge-ai-main',
            'owner_id' => $ownerUser->id,
            'stripe_id' => 'cus_test_' . Str::random(10),
            'trial_ends_at' => now()->addDays(14),
        ]);

        // 2. Create more organizations with owners
        $orgNames = [
            'Tech Innovators',
            'Cloud Solutions Ltd',
            'NextGen Systems',
            'SaaS Builders',
            'WebCraft Agency',
        ];

        foreach ($orgNames as $name) {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Owner');

            Organization::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'owner_id' => $user->id,
                'stripe_id' => 'cus_test_' . Str::random(10),
                'trial_ends_at' => now()->addDays(fake()->numberBetween(0, 30)),
            ]);
        }
    }
}
