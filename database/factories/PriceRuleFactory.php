<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use CastMart\Trendyol\Models\PriceRule;
use CastMart\Marketplace\Models\MarketplaceAccount;

class PriceRuleFactory extends Factory
{
    protected $model = PriceRule::class;

    public function definition(): array
    {
        return [
            'marketplace_account_id' => MarketplaceAccount::factory(),
            'name' => $this->faker->sentence(3),
            'trigger' => $this->faker->randomElement(['competitor_cheaper', 'buybox_lost', 'stock_low', 'competitor_stock_zero']),
            'action' => $this->faker->randomElement(['match_minus', 'decrease_percent', 'increase_percent', 'set_price']),
            'action_value' => $this->faker->randomFloat(2, 1, 10),
            'scope' => 'all',
            'scope_data' => null,
            'sku_filter' => null,
            'min_price' => $this->faker->randomFloat(2, 10, 50),
            'max_price' => $this->faker->randomFloat(2, 500, 1000),
            'priority' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
            'trigger_count' => $this->faker->numberBetween(0, 100),
            'last_triggered_at' => $this->faker->optional()->dateTimeThisMonth(),
        ];
    }

    public function competitorCheaper(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => 'competitor_cheaper',
            'action' => 'match_minus',
            'action_value' => 1.00,
        ]);
    }

    public function buyboxLost(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => 'buybox_lost',
            'action' => 'decrease_percent',
            'action_value' => 5.00,
        ]);
    }

    public function stockLow(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => 'stock_low',
            'action' => 'increase_percent',
            'action_value' => 10.00,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 1,
        ]);
    }
}
