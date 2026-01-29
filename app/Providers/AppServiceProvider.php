<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Product;
use App\Models\Stocktaking;
use App\Models\Waste;
use App\Observers\ProductObserver;
use App\Observers\StocktakingObserver;
use App\Observers\WasteObserver;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Stocktaking::observe(StocktakingObserver::class);
        Waste::observe(WasteObserver::class);

        Product::observe(ProductObserver::class);
        $this->configureTable();
    }

    private function configureTable(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->striped()
                ->deferLoading();
        });
    }
}
