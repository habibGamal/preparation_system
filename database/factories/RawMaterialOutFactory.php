<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MaterialEntranceStatus;
use App\Models\Consumer;
use App\Models\RawMaterialOut;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RawMaterialOut>
 */
final class RawMaterialOutFactory extends Factory
{
    protected $model = RawMaterialOut::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'consumer_id' => Consumer::factory(),
            'status' => fake()->randomElement(MaterialEntranceStatus::cases()),
            'total' => fake()->randomFloat(2, 100, 10000),
            'closed_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => MaterialEntranceStatus::Draft,
            'closed_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => MaterialEntranceStatus::Closed,
            'closed_at' => now(),
        ]);
    }
}
