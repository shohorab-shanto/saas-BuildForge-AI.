<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionsSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();
        $plans = ['Free', 'Starter', 'Pro', 'Enterprise'];

        foreach ($organizations as $org) {
            $plan = fake()->randomElement($plans);
            
            DB::table('subscriptions')->insert([
                'organization_id' => $org->id,
                'type' => 'default',
                'stripe_id' => 'sub_test_' . fake()->uuid(),
                'stripe_status' => 'active',
                'stripe_price' => 'price_' . fake()->uuid(),
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
