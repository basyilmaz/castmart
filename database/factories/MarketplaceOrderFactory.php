<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use CastMart\Marketplace\Models\MarketplaceOrder;
use CastMart\Marketplace\Models\MarketplaceAccount;

class MarketplaceOrderFactory extends Factory
{
    protected $model = MarketplaceOrder::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 50, 500);
        
        return [
            'account_id' => MarketplaceAccount::factory(),
            'external_order_id' => $this->faker->numerify('##########'),
            'external_order_number' => $this->faker->numerify('T########'),
            'package_id' => $this->faker->numerify('##########'),
            'status' => $this->faker->randomElement(['new', 'processing', 'shipped', 'delivered', 'cancelled']),
            'total_amount' => $unitPrice * $quantity,
            'customer_data' => [
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName(),
                'email' => $this->faker->safeEmail(),
                'phone' => $this->faker->phoneNumber(),
                'city' => $this->faker->city(),
                'district' => $this->faker->streetName(),
                'address' => $this->faker->address(),
            ],
            'items_data' => [
                [
                    'barcode' => $this->faker->ean13(),
                    'productName' => $this->faker->sentence(3),
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'categoryName' => $this->faker->word(),
                ],
            ],
            'order_data' => [
                'commissionRate' => $this->faker->randomFloat(2, 8, 20),
            ],
            'cargo_provider' => $this->faker->randomElement(['ARASKARGOMARKET', 'YURTICI', 'MNG']),
            'tracking_number' => $this->faker->numerify('TR##########'),
        ];
    }

    public function new(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
            'tracking_number' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'tracking_number' => null,
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'tracking_number' => $this->faker->numerify('TR##########'),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'tracking_number' => $this->faker->numerify('TR##########'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'returned',
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $this->faker->randomFloat(2, 1000, 5000),
        ]);
    }
}
