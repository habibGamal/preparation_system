<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Consumer;
use Illuminate\Database\Seeder;

final class ConsumerSeeder extends Seeder
{
    public function run(): void
    {
        Consumer::factory()->count(20)->create();
    }
}
