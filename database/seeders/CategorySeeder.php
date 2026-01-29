<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
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
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
