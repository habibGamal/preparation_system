<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\MaterialEntranceStatus;
use App\Enums\ProductType;
use App\Filament\Exports\ManufacturedConsumerOutReportExporter;
use App\Filament\Exports\RawConsumerOutReportExporter;
use App\Models\Consumer;
use App\Models\ManufacturedMaterialOutItem;
use App\Models\RawMaterialOutItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\DatePicker;
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
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class ConsumerOutReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static string|UnitEnum|null $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'تقرير إخراجات المستهلكين';

    protected static ?string $title = 'تقرير إخراجات مستهلك';

    protected string $view = 'filament.pages.consumer-out-report';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public string $activeTab = 'raw';

    public ?string $periodLabel = null;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $today = now()->toDateString();

        $this->form->fill([
            'consumer_id' => null,
            'mode' => 'single',
            'single_date' => $today,
            'start_date' => $today,
            'end_date' => $today,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('فلاتر التقرير')
                        ->description(fn (): ?string => $this->periodLabel !== null ? 'الفترة: ' . $this->periodLabel : null)
                        ->schema([
                            Select::make('consumer_id')
                                ->label('المستهلك')
                                ->options(fn (): array => Consumer::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload()
                                ->required(),

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
        /** @var \Illuminate\Database\Eloquent\Builder $reportQuery */
        $reportQuery = $this->buildConsumerItemsQuery();

        return $table
            ->query($reportQuery)
            ->columns([
                TextColumn::make('product_name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_quantity')
                    ->label('إجمالي الكمية')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('average_price')
                    ->label('متوسط السعر')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_value')
                    ->label('القيمة الإجمالية')
                    ->money('EGP')
                    ->sortable(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter($this->activeTab === 'manufactured' ? ManufacturedConsumerOutReportExporter::class : RawConsumerOutReportExporter::class)
                    ->label('تصدير')
                    ->color('primary'),
            ])
            ->defaultSort('product_name');
    }

    public function updatedActiveTab(): void
    {
        $this->resetTable();
    }

    public function generateReport(): void
    {
        $data = validator($this->form->getState(), [
            'consumer_id' => ['required', 'exists:consumers,id'],
            'mode' => ['required', 'in:single,range'],
            'single_date' => ['required_if:mode,single', 'date'],
            'start_date' => ['required_if:mode,range', 'date'],
            'end_date' => ['required_if:mode,range', 'date', 'after_or_equal:start_date'],
        ])->validate();

        [$startAt, $endAt] = $this->resolvePeriodFromData($data);

        $this->periodLabel = $startAt->toDateString() === $endAt->toDateString()
            ? $startAt->toDateString()
            : $startAt->toDateString() . ' - ' . $endAt->toDateString();

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

    private function buildConsumerItemsQuery()
    {
        [$startAt, $endAt] = $this->resolvePeriod();
        $consumerId = (int) ($this->data['consumer_id'] ?? 0);

        if ($this->activeTab === 'manufactured') {
            $itemTable = 'manufactured_material_out_items';
            $outTable = 'manufactured_material_outs';
            $outForeignKey = 'manufactured_material_out_id';
            $productType = ProductType::Manufactured->value;
            $query = ManufacturedMaterialOutItem::query();
        } else {
            $itemTable = 'raw_material_out_items';
            $outTable = 'raw_material_outs';
            $outForeignKey = 'raw_material_out_id';
            $productType = ProductType::Raw->value;
            $query = RawMaterialOutItem::query();
        }

        return $query
            ->join($outTable . ' as material_outs', 'material_outs.id', '=', $itemTable . '.' . $outForeignKey)
            ->join('products', 'products.id', '=', $itemTable . '.product_id')
            ->join('consumers', 'consumers.id', '=', 'material_outs.consumer_id')
            ->where('material_outs.status', MaterialEntranceStatus::Closed->value)
            ->whereNotNull('material_outs.closed_at')
            ->where('products.type', $productType)
            ->whereBetween('material_outs.closed_at', [$startAt->toDateTimeString(), $endAt->toDateTimeString()])
            ->when(
                $consumerId > 0,
                fn ($query) => $query->where('material_outs.consumer_id', $consumerId),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->selectRaw('MIN(' . $itemTable . '.id) as id')
            ->selectRaw('products.id as product_id')
            ->selectRaw('products.name as product_name')
            ->selectRaw('ROUND(SUM(' . $itemTable . '.quantity), 2) as total_quantity')
            ->selectRaw('ROUND(AVG(' . $itemTable . '.price), 2) as average_price')
            ->selectRaw('ROUND(SUM(' . $itemTable . '.quantity * ' . $itemTable . '.price), 2) as total_value')
            ->groupBy('products.id', 'products.name');
    }
}
