<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'منتجات ألبان',
                'مخبوزات',
                'حلويات',
                'مشروبات',
                'خضروات',
                'فواكه',
                'لحوم',
                'أسماك',
                'بقوليات',
                'توابل',
                'معلبات',
                'مواد تعبئة',
            ]),
        ];
    }
}
