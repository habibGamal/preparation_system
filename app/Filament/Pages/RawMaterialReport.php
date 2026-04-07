<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\MaterialEntranceStatus;
use App\Enums\ProductType;
use App\Filament\Exports\RawMaterialReportExporter;
use App\Models\Product;
use App\Models\RawMaterialEntranceItem;
use App\Models\RawMaterialOutItem;
use App\Models\StocktakingItem;
use App\Models\WastedItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class RawMaterialReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'تقرير المواد الخام';

    protected static ?string $title = 'تقرير حركة المواد الخام';

    protected string $view = 'filament.pages.raw-material-report';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * @var array<string, float>|null
     */
    public ?array $report = null;

    public ?string $periodLabel = null;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $today = now()->toDateString();

        $this->form->fill([
            'mode' => 'single',
            'single_date' => $today,
            'start_date' => $today,
            'end_date' => $today,
        ]);

        $this->generateReport();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('فلاتر التقرير')
                        ->schema([
                            Select::make('mode')
                                ->label('نوع الفترة')
                                ->options([
                                    'single' => 'يوم واحد',
                                    'range' => 'فترة (من - إلى)',
                                ])
                                ->default('single')
                                ->live()
                                ->required(),

                            DatePicker::make('single_date')
                                ->label('التاريخ')
                                ->visible(fn (callable $get): bool => $get('mode') === 'single')
                                ->required(fn (callable $get): bool => $get('mode') === 'single')
                                ->native(false),

                            DatePicker::make('start_date')
                                ->label('من تاريخ')
                                ->visible(fn (callable $get): bool => $get('mode') === 'range')
                                ->required(fn (callable $get): bool => $get('mode') === 'range')
                                ->native(false),

                            DatePicker::make('end_date')
                                ->label('إلى تاريخ')
                                ->visible(fn (callable $get): bool => $get('mode') === 'range')
                                ->required(fn (callable $get): bool => $get('mode') === 'range')
                                ->minDate(fn (callable $get): ?string => $get('start_date'))
                                ->native(false),
                        ])
                        ->columns(3),

                    Section::make('ملخص التقرير')
                        ->description(fn (): ?string => $this->periodLabel !== null ? 'الفترة: ' . $this->periodLabel : null)
                        ->schema([
                            Placeholder::make('start_quantity')
                                ->label('رصيد بداية الفترة')
                                ->content(fn (): string => $this->formatQuantity($this->report['start_quantity'] ?? null)),

                            Placeholder::make('inlet_quantity')
                                ->label('كمية الإدخال خلال الفترة')
                                ->content(fn (): string => $this->formatQuantity($this->report['inlet_quantity'] ?? null)),

                            Placeholder::make('outlet_quantity')
                                ->label('كمية الإخراج خلال الفترة')
                                ->content(fn (): string => $this->formatQuantity($this->report['outlet_quantity'] ?? null)),

                            Placeholder::make('stocktaking_quantity')
                                ->label('كمية تسوية الجرد خلال الفترة')
                                ->content(fn (): string => $this->formatQuantity($this->report['stocktaking_quantity'] ?? null)),

                            Placeholder::make('waste_quantity')
                                ->label('كمية التالف خلال الفترة')
                                ->content(fn (): string => $this->formatQuantity($this->report['waste_quantity'] ?? null)),

                            Placeholder::make('end_quantity')
                                ->label('رصيد نهاية الفترة')
                                ->content(fn (): string => $this->formatQuantity($this->report['end_quantity'] ?? null)),
                        ])
                        ->columns(3),
                ])
                    ->livewireSubmitHandler('generateReport')
                    ->footer([
                        Actions::make([
                            Action::make('generate')
                                ->label('عرض التقرير')
                                ->submit('generateReport')
                                ->icon('heroicon-o-funnel'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        [$startAt, $endAt] = $this->resolvePeriod();
        /** @var \Illuminate\Database\Eloquent\Builder $reportQuery */
        $reportQuery = $this->buildProductReportQuery($startAt, $endAt);

        return $table
            ->query($reportQuery)
            ->columns([
                TextColumn::make('name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_quantity')
                    ->label('رصيد البداية')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('inlet_quantity')
                    ->label('الإدخال')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('outlet_quantity')
                    ->label('الإخراج')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('stocktaking_quantity')
                    ->label('تسوية الجرد')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('waste_quantity')
                    ->label('التالف')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('end_quantity')
                    ->label('رصيد النهاية')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(RawMaterialReportExporter::class)
                    ->label('تصدير')
                    ->color('primary'),
            ])
            ->defaultSort('name');
    }

    public function generateReport(): void
    {
        $data = validator($this->form->getState(), [
            'mode' => ['required', 'in:single,range'],
            'single_date' => ['required_if:mode,single', 'date'],
            'start_date' => ['required_if:mode,range', 'date'],
            'end_date' => ['required_if:mode,range', 'date', 'after_or_equal:start_date'],
        ])->validate();

        [$startAt, $endAt] = $this->resolvePeriodFromData($data);

        $this->periodLabel = $startAt->toDateString() === $endAt->toDateString()
            ? $startAt->toDateString()
            : $startAt->toDateString() . ' - ' . $endAt->toDateString();

        $this->report = $this->calculateSummaryFromDatabase($startAt, $endAt);

        $this->resetTable();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriodFromData(array $data): array
    {
        if ((string) $data['mode'] === 'range') {
            $start = Carbon::parse((string) $data['start_date'])->startOfDay();
            $end = Carbon::parse((string) $data['end_date'])->endOfDay();

            return [$start, $end];
        }

        $singleDate = Carbon::parse((string) $data['single_date']);

        return [$singleDate->copy()->startOfDay(), $singleDate->copy()->endOfDay()];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriod(): array
    {
        $mode = (string) ($this->data['mode'] ?? 'single');

        if ($mode === 'range') {
            $start = Carbon::parse((string) ($this->data['start_date'] ?? now()->toDateString()))->startOfDay();
            $end = Carbon::parse((string) ($this->data['end_date'] ?? now()->toDateString()))->endOfDay();

            return [$start, $end];
        }

        $singleDate = Carbon::parse((string) ($this->data['single_date'] ?? now()->toDateString()));

        return [$singleDate->copy()->startOfDay(), $singleDate->copy()->endOfDay()];
    }

    private function buildProductReportQuery(Carbon $startAt, Carbon $endAt)
    {
        $startDateTime = $startAt->toDateTimeString();
        $endDateTime = $endAt->toDateTimeString();

        $inletBeforeSub = RawMaterialEntranceItem::query()
            ->selectRaw('raw_material_entrance_items.product_id, COALESCE(SUM(raw_material_entrance_items.quantity), 0) as quantity_total')
            ->join('raw_material_entrances', 'raw_material_entrances.id', '=', 'raw_material_entrance_items.raw_material_entrance_id')
            ->where('raw_material_entrances.status', MaterialEntranceStatus::Closed->value)
            ->whereNotNull('raw_material_entrances.closed_at')
            ->where('raw_material_entrances.closed_at', '<', $startDateTime)
            ->groupBy('raw_material_entrance_items.product_id');

        $inletPeriodSub = RawMaterialEntranceItem::query()
            ->selectRaw('raw_material_entrance_items.product_id, COALESCE(SUM(raw_material_entrance_items.quantity), 0) as quantity_total')
            ->join('raw_material_entrances', 'raw_material_entrances.id', '=', 'raw_material_entrance_items.raw_material_entrance_id')
            ->where('raw_material_entrances.status', MaterialEntranceStatus::Closed->value)
            ->whereNotNull('raw_material_entrances.closed_at')
            ->whereBetween('raw_material_entrances.closed_at', [$startDateTime, $endDateTime])
            ->groupBy('raw_material_entrance_items.product_id');

        $outletBeforeSub = RawMaterialOutItem::query()
            ->selectRaw('raw_material_out_items.product_id, COALESCE(SUM(raw_material_out_items.quantity), 0) as quantity_total')
            ->join('raw_material_outs', 'raw_material_outs.id', '=', 'raw_material_out_items.raw_material_out_id')
            ->where('raw_material_outs.status', MaterialEntranceStatus::Closed->value)
            ->whereNotNull('raw_material_outs.closed_at')
            ->where('raw_material_outs.closed_at', '<', $startDateTime)
            ->groupBy('raw_material_out_items.product_id');

        $outletPeriodSub = RawMaterialOutItem::query()
            ->selectRaw('raw_material_out_items.product_id, COALESCE(SUM(raw_material_out_items.quantity), 0) as quantity_total')
            ->join('raw_material_outs', 'raw_material_outs.id', '=', 'raw_material_out_items.raw_material_out_id')
            ->where('raw_material_outs.status', MaterialEntranceStatus::Closed->value)
            ->whereNotNull('raw_material_outs.closed_at')
            ->whereBetween('raw_material_outs.closed_at', [$startDateTime, $endDateTime])
            ->groupBy('raw_material_out_items.product_id');

        $stocktakingBeforeSub = StocktakingItem::query()
            ->selectRaw('stocktaking_items.product_id, COALESCE(SUM(stocktaking_items.real_quantity - stocktaking_items.stock_quantity), 0) as quantity_total')
            ->join('stocktakings', 'stocktakings.id', '=', 'stocktaking_items.stocktaking_id')
            ->where('stocktakings.product_type', ProductType::Raw->value)
            ->whereNotNull('stocktakings.closed_at')
            ->where('stocktakings.closed_at', '<', $startDateTime)
            ->groupBy('stocktaking_items.product_id');

        $stocktakingPeriodSub = StocktakingItem::query()
            ->selectRaw('stocktaking_items.product_id, COALESCE(SUM(stocktaking_items.real_quantity - stocktaking_items.stock_quantity), 0) as quantity_total')
            ->join('stocktakings', 'stocktakings.id', '=', 'stocktaking_items.stocktaking_id')
            ->where('stocktakings.product_type', ProductType::Raw->value)
            ->whereNotNull('stocktakings.closed_at')
            ->whereBetween('stocktakings.closed_at', [$startDateTime, $endDateTime])
            ->groupBy('stocktaking_items.product_id');

        $wasteBeforeSub = WastedItem::query()
            ->selectRaw('wasted_items.product_id, COALESCE(SUM(wasted_items.quantity), 0) as quantity_total')
            ->join('wastes', 'wastes.id', '=', 'wasted_items.waste_id')
            ->where('wastes.type', ProductType::Raw->value)
            ->whereNotNull('wastes.closed_at')
            ->where('wastes.closed_at', '<', $startDateTime)
            ->groupBy('wasted_items.product_id');

        $wastePeriodSub = WastedItem::query()
            ->selectRaw('wasted_items.product_id, COALESCE(SUM(wasted_items.quantity), 0) as quantity_total')
            ->join('wastes', 'wastes.id', '=', 'wasted_items.waste_id')
            ->where('wastes.type', ProductType::Raw->value)
            ->whereNotNull('wastes.closed_at')
            ->whereBetween('wastes.closed_at', [$startDateTime, $endDateTime])
            ->groupBy('wasted_items.product_id');

        $startExpression = 'COALESCE(inlet_before.quantity_total, 0) - COALESCE(outlet_before.quantity_total, 0) + COALESCE(stocktaking_before.quantity_total, 0) - COALESCE(waste_before.quantity_total, 0)';
        $endExpression = '(' . $startExpression . ') + COALESCE(inlet_period.quantity_total, 0) - COALESCE(outlet_period.quantity_total, 0) + COALESCE(stocktaking_period.quantity_total, 0) - COALESCE(waste_period.quantity_total, 0)';

        return Product::query()
            ->where('products.type', ProductType::Raw->value)
            ->select(['products.id', 'products.name'])
            ->leftJoinSub($inletBeforeSub, 'inlet_before', fn ($join) => $join->on('inlet_before.product_id', '=', 'products.id'))
            ->leftJoinSub($inletPeriodSub, 'inlet_period', fn ($join) => $join->on('inlet_period.product_id', '=', 'products.id'))
            ->leftJoinSub($outletBeforeSub, 'outlet_before', fn ($join) => $join->on('outlet_before.product_id', '=', 'products.id'))
            ->leftJoinSub($outletPeriodSub, 'outlet_period', fn ($join) => $join->on('outlet_period.product_id', '=', 'products.id'))
            ->leftJoinSub($stocktakingBeforeSub, 'stocktaking_before', fn ($join) => $join->on('stocktaking_before.product_id', '=', 'products.id'))
            ->leftJoinSub($stocktakingPeriodSub, 'stocktaking_period', fn ($join) => $join->on('stocktaking_period.product_id', '=', 'products.id'))
            ->leftJoinSub($wasteBeforeSub, 'waste_before', fn ($join) => $join->on('waste_before.product_id', '=', 'products.id'))
            ->leftJoinSub($wastePeriodSub, 'waste_period', fn ($join) => $join->on('waste_period.product_id', '=', 'products.id'))
            ->selectRaw('ROUND(' . $startExpression . ', 2) as start_quantity')
            ->selectRaw('ROUND(COALESCE(inlet_period.quantity_total, 0), 2) as inlet_quantity')
            ->selectRaw('ROUND(COALESCE(outlet_period.quantity_total, 0), 2) as outlet_quantity')
            ->selectRaw('ROUND(COALESCE(stocktaking_period.quantity_total, 0), 2) as stocktaking_quantity')
            ->selectRaw('ROUND(COALESCE(waste_period.quantity_total, 0), 2) as waste_quantity')
            ->selectRaw('ROUND(' . $endExpression . ', 2) as end_quantity');
    }

    /**
     * @return array<string, float>
     */
    private function calculateSummaryFromDatabase(Carbon $startAt, Carbon $endAt): array
    {
        $summary = DB::query()
            ->fromSub($this->buildProductReportQuery($startAt, $endAt), 'report')
            ->selectRaw('COALESCE(SUM(start_quantity), 0) as start_quantity')
            ->selectRaw('COALESCE(SUM(inlet_quantity), 0) as inlet_quantity')
            ->selectRaw('COALESCE(SUM(outlet_quantity), 0) as outlet_quantity')
            ->selectRaw('COALESCE(SUM(stocktaking_quantity), 0) as stocktaking_quantity')
            ->selectRaw('COALESCE(SUM(waste_quantity), 0) as waste_quantity')
            ->selectRaw('COALESCE(SUM(end_quantity), 0) as end_quantity')
            ->first();

        return [
            'start_quantity' => round((float) ($summary->start_quantity ?? 0), 2),
            'inlet_quantity' => round((float) ($summary->inlet_quantity ?? 0), 2),
            'outlet_quantity' => round((float) ($summary->outlet_quantity ?? 0), 2),
            'stocktaking_quantity' => round((float) ($summary->stocktaking_quantity ?? 0), 2),
            'waste_quantity' => round((float) ($summary->waste_quantity ?? 0), 2),
            'end_quantity' => round((float) ($summary->end_quantity ?? 0), 2),
        ];
    }

    private function formatQuantity(?float $value): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format($value, 2, '.', '');
    }
}
