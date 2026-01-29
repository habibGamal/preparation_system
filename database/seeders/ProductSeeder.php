<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Enums\ProductUnit;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');

            return;
        }

        // Create raw materials
        $rawMaterials = [
            ['name' => 'دقيق أبيض', 'unit' => ProductUnit::Kilogram, 'category' => 'مخبوزات'],
            ['name' => 'سكر', 'unit' => ProductUnit::Kilogram, 'category' => 'حلويات'],
            ['name' => 'ملح', 'unit' => ProductUnit::Kilogram, 'category' => 'توابل'],
            ['name' => 'زيت نباتي', 'unit' => ProductUnit::Liter, 'category' => 'مواد تعبئة'],
            ['name' => 'حليب طازج', 'unit' => ProductUnit::Liter, 'category' => 'منتجات ألبان'],
            ['name' => 'بيض', 'unit' => ProductUnit::Package, 'category' => 'منتجات ألبان'],
            ['name' => 'زبدة', 'unit' => ProductUnit::Kilogram, 'category' => 'منتجات ألبان'],
            ['name' => 'خميرة', 'unit' => ProductUnit::Gram, 'category' => 'مخبوزات'],
            ['name' => 'كاكاو', 'unit' => ProductUnit::Kilogram, 'category' => 'حلويات'],
            ['name' => 'فانيليا', 'unit' => ProductUnit::Gram, 'category' => 'توابل'],
        ];

        foreach ($rawMaterials as $material) {
            $category = $categories->firstWhere('name', $material['category']);

            if ($category !== null) {
                Product::create([
                    'name' => $material['name'],
                    'category_id' => $category->id,
                    'barcode' => fake()->unique()->ean13(),
                    'price' => fake()->randomFloat(2, 20, 200),
                    'cost' => fake()->randomFloat(2, 10, 150),
                    'min_stock' => fake()->numberBetween(50, 200),
                    'avg_purchase_quantity' => fake()->numberBetween(100, 500),
                    'type' => ProductType::Raw,
                    'unit' => $material['unit'],
                ]);
            }
        }

        // Create manufactured products
        $manufacturedProducts = [
            ['name' => 'خبز أبيض', 'unit' => ProductUnit::Piece, 'category' => 'مخبوزات'],
            ['name' => 'خبز أسمر', 'unit' => ProductUnit::Piece, 'category' => 'مخبوزات'],
            ['name' => 'كرواسون', 'unit' => ProductUnit::Piece, 'category' => 'مخبوزات'],
            ['name' => 'كيك شوكولاتة', 'unit' => ProductUnit::Piece, 'category' => 'حلويات'],
            ['name' => 'بسكويت', 'unit' => ProductUnit::Package, 'category' => 'حلويات'],
            ['name' => 'جبنة بيضاء', 'unit' => ProductUnit::Kilogram, 'category' => 'منتجات ألبان'],
            ['name' => 'زبادي', 'unit' => ProductUnit::Box, 'category' => 'منتجات ألبان'],
            ['name' => 'عصير برتقال', 'unit' => ProductUnit::Liter, 'category' => 'مشروبات'],
        ];

        foreach ($manufacturedProducts as $product) {
            $category = $categories->firstWhere('name', $product['category']);

            if ($category !== null) {
                Product::create([
                    'name' => $product['name'],
                    'category_id' => $category->id,
                    'barcode' => fake()->unique()->ean13(),
                    'price' => fake()->randomFloat(2, 30, 300),
                    'cost' => fake()->randomFloat(2, 15, 200),
                    'min_stock' => fake()->numberBetween(20, 100),
                    'avg_purchase_quantity' => fake()->numberBetween(50, 300),
                    'type' => ProductType::Manufactured,
                    'unit' => $product['unit'],
                ]);
            }
        }
    }
}
