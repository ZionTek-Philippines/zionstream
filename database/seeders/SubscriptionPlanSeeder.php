<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'           => 'Basic Monthly',
                'slug'           => 'basic-monthly',
                'description'    => 'Access to all live streams and VOD replays.',
                'price'          => 299.00,
                'billing_period' => 'monthly',
                'features'       => [
                    'Watch all live streams',
                    'Access VOD replays',
                    'Live chat participation',
                    'Product claim access',
                ],
                'is_active'  => true,
                'sort_order' => 1,
            ],
            [
                'name'           => 'Basic Yearly',
                'slug'           => 'basic-yearly',
                'description'    => 'Same as monthly — save 2 months by paying yearly.',
                'price'          => 2990.00,
                'billing_period' => 'yearly',
                'features'       => [
                    'Watch all live streams',
                    'Access VOD replays',
                    'Live chat participation',
                    'Product claim access',
                    '2 months free vs monthly',
                ],
                'is_active'  => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
