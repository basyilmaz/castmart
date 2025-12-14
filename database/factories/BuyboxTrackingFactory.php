<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use CastMart\Trendyol\Models\BuyboxTracking;
use CastMart\Marketplace\Models\MarketplaceAccount;

class BuyboxTrackingFactory extends Factory
{
    protected $model = BuyboxTracking::class;

    public function definition(): array
    {
        $ourPrice = $this->faker->randomFloat(2, 50, 500);
        $isWinner = $this->faker->boolean(60); // %60 kazanma oranı
        
        return [
            'marketplace_account_id' => MarketplaceAccount::factory(),
            'barcode' => $this->faker->ean13(),
            'product_url' => $this->faker->url(),
            'our_price' => $ourPrice,
            'buybox_price' => $isWinner ? $ourPrice : $ourPrice * 0.95,
            'competitor_price' => $ourPrice * $this->faker->randomFloat(2, 0.85, 1.15),
            'is_winner' => $isWinner,
            'competitor_count' => $this->faker->numberBetween(1, 10),
            'seller_name' => $isWinner ? 'Biz' : $this->faker->company(),
            'raw_data' => [],
        ];
    }

    public function winner(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['our_price'] ?? 100;
            return [
                'is_winner' => true,
                'buybox_price' => $price,
                'seller_name' => 'Biz',
            ];
        });
    }

    public function loser(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['our_price'] ?? 100;
            return [
                'is_winner' => false,
                'buybox_price' => $price * 0.95,
                'competitor_price' => $price * 0.95,
                'seller_name' => $this->faker->company(),
            ];
        });
    }

    public function noCompetitor(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_winner' => true,
            'competitor_count' => 0,
            'competitor_price' => null,
        ]);
    }
}
