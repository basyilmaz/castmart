<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Marketplace\Models\MarketplaceAccount;

class MarketplaceListingFactory extends Factory
{
    protected $model = MarketplaceListing::class;

    public function definition(): array
    {
        $listPrice = $this->faker->randomFloat(2, 50, 1000);
        
        return [
            'account_id' => MarketplaceAccount::factory(),
            'barcode' => $this->faker->ean13(),
            'title' => $this->faker->sentence(4),
            'brand' => $this->faker->company(),
            'category_id' => $this->faker->numberBetween(1000, 9999),
            'stock' => $this->faker->numberBetween(0, 500),
            'sale_price' => $listPrice * 0.85,
            'list_price' => $listPrice,
            'vat_rate' => $this->faker->randomElement([1, 8, 18]),
            'desi' => $this->faker->randomFloat(1, 1, 30),
            'status' => $this->faker->randomElement(['active', 'passive', 'draft']),
            'external_id' => $this->faker->numerify('###########'),
            'description' => $this->faker->paragraph(3),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'stock' => $this->faker->numberBetween(10, 500),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'stock' => 0,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'stock' => $this->faker->numberBetween(1, 5),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'external_id' => null,
        ]);
    }
}
