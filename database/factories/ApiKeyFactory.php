<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ApiKey;
use Illuminate\Support\Str;

class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        $plainKey = 'cast_' . Str::random(32);
        
        return [
            'name' => $this->faker->words(2, true) . ' API Key',
            'key' => hash('sha256', $plainKey),
            'prefix' => substr($plainKey, 0, 8),
            'user_id' => null,
            'user_type' => 'app',
            'permissions' => null, // null = tüm izinler
            'rate_limits' => null,
            'ip_whitelist' => null,
            'last_used_at' => null,
            'last_used_ip' => null,
            'expires_at' => null,
            'is_active' => true,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function withPermissions(array $permissions): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => $permissions,
        ]);
    }

    public function withRateLimits(int $limit = 100, int $decay = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'rate_limits' => [
                'limit' => $limit,
                'decay' => $decay,
            ],
        ]);
    }

    public function withIpWhitelist(array $ips): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_whitelist' => $ips,
        ]);
    }

    public function recentlyUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => now()->subMinutes($this->faker->numberBetween(1, 60)),
            'last_used_ip' => $this->faker->ipv4(),
        ]);
    }
}
