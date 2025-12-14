<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use CastMart\Marketplace\Models\MarketplaceAccount;

class MarketplaceAccountFactory extends Factory
{
    protected $model = MarketplaceAccount::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Mağaza',
            'marketplace' => 'trendyol',
            'credentials' => [
                'supplier_id' => $this->faker->numerify('######'),
                'api_key' => $this->faker->uuid(),
                'api_secret' => $this->faker->sha256(),
            ],
            'is_active' => true,
            'settings' => [
                'auto_sync' => true,
                'sync_interval' => 15,
                'auto_price_update' => false,
            ],
        ];
    }

    public function trendyol(): static
    {
        return $this->state(fn (array $attributes) => [
            'marketplace' => 'trendyol',
        ]);
    }

    public function hepsiburada(): static
    {
        return $this->state(fn (array $attributes) => [
            'marketplace' => 'hepsiburada',
        ]);
    }

    public function n11(): static
    {
        return $this->state(fn (array $attributes) => [
            'marketplace' => 'n11',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
