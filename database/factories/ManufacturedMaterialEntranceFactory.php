<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MaterialEntranceStatus;
use App\Models\ManufacturedMaterialEntrance;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManufacturedMaterialEntrance>
 */
final class ManufacturedMaterialEntranceFactory extends Factory
{
    protected $model = ManufacturedMaterialEntrance::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'supplier_id' => Supplier::factory(),
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
