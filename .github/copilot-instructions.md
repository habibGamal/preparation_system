# AI Coding Instructions - Larament (Laravel 12.x + FilamentPHP 4.x)

## Architecture Overview

This is a **Laravel 12.x + FilamentPHP 4.x** admin panel application (Arabic/RTL interface) for inventory and raw material management. The codebase uses **PHP 8.3+** with strict typing and enhanced Laravel defaults via `nunomaduro/essentials` (strict models, auto-eager loading, immutable dates).

## Filament Resource Structure (Critical Pattern)

Resources are organized into **subdirectories** with static configuration classes - NOT inline configuration:

```
app/Filament/Resources/
  ProductResource.php              # Main resource (navigation, pages)
  ProductResource/
    Schemas/ProductForm.php        # Form configuration (static configure method)
    Tables/ProductsTable.php       # Table configuration (static configure method)
    Pages/
      ListProducts.php
      CreateProduct.php
      EditProduct.php
```

**Key Pattern:**
- Resource files call `ProductForm::configure($schema)` and `ProductsTable::configure($table)`
- Form/Table classes have `public static function configure()` methods returning configured objects
- See [ProductResource.php](app/Filament/Resources/ProductResource.php), [ProductForm.php](app/Filament/Resources/ProductResource/Schemas/ProductForm.php), [ProductsTable.php](app/Filament/Resources/ProductResource/Tables/ProductsTable.php) for examples

## Code Standards (Enforced by CI)

1. **Strict Typing Everywhere:** Every PHP file starts with `declare(strict_types=1);`
2. **Final Classes:** All classes are `final` (Pint rule)
3. **Strict Comparisons:** Use `===` and `!==` (Pint rule)
4. **Type Declarations:** All method parameters and return types must be explicitly typed
5. **No Debug Code:** Never use `dd()`, `dump()`, `die()`, `ray()` in `app/` (ArchTest enforced)
6. **No Direct `env()`:** Only use `env()` in `config/` files (ArchTest enforced)

See [pint.json](pint.json) for full ruleset.

## Model Conventions

- **Fillable over Guarded:** Models use `$fillable` arrays (essentials config has `Unguard::class => true` but we explicitly define fillable)
- **Enum Casting:** Status fields use PHP enums with badge labels (e.g., `MaterialEntranceStatus`, `ProductType`)
- **Observers:** Model lifecycle events handled via observers (e.g., `ProductObserver` auto-creates inventory record)
- **Relationships:** Always define inverse relationships (e.g., if Product `belongsTo` Category, Category must have `hasMany` Products)

Example observer registration in [AppServiceProvider.php](app/Providers/AppServiceProvider.php):
```php
Product::observe(ProductObserver::class);
```

## Filament Customization Patterns

### Global Table Configuration
Set in [AppServiceProvider.php](app/Providers/AppServiceProvider.php):
```php
Table::configureUsing(fn (Table $table) => $table->striped()->deferLoading());
```

### Arabic Labels
All UI text uses Arabic labels:
```php
TextColumn::make('name')->label('الاسم')
SelectFilter::make('status')->label('الحالة')
```

### Money Format
Always use `->money('EGP')` for currency fields.

### Import/Export
Resources with large datasets include import/export actions (see [ProductsTable.php](app/Filament/Resources/ProductResource/Tables/ProductsTable.php) `headerActions`).

### Complex Business Logic in Resource Pages
See [CreateRawMaterialEntrance.php](app/Filament/Resources/RawMaterialEntranceResource/Pages/CreateRawMaterialEntrance.php) for patterns:
- `mutateFormDataBeforeCreate()` - calculate totals, set defaults
- `afterCreate()` - update inventory, set timestamps
- Wrap multi-table updates in `DB::transaction()`

## Testing Standards (PEST 4.x)

All tests use `RefreshDatabase` trait. Follow existing patterns in [tests/Feature/Filament/Resources/UserResourceTest.php](tests/Feature/Filament/Resources/UserResourceTest.php):

**Standard Test Structure:**
```php
beforeEach(function () {
    User::truncate(); // If needed for clean slate
});

it('can render the index page', function () {
    livewire(ListUsers::class)->assertOk();
});

it('can create a user', function () {
    $user = User::factory()->make();
    livewire(CreateUser::class)
        ->fillForm(['name' => $user->name, 'email' => $user->email, ...])
        ->call('create')
        ->assertNotified();
    assertDatabaseHas(User::class, ['name' => $user->name]);
});
```

**Browser Tests:** Use Playwright via `visit()`, `fill()`, `submit()` (see [tests/Browser/Filament/Auth/LoginTest.php](tests/Browser/Filament/Auth/LoginTest.php)).

## Development Workflow Commands

```bash
composer review          # Runs Pint → Rector → PHPStan → Pest (use before commits)
composer dev            # Starts server, queue, logs, and Vite concurrently
composer pest           # Run tests with parallel execution
php artisan db:seed     # Seed database with test data
```

## Migration Stubs

Custom stubs in [stubs/](stubs/) **remove `down()` methods** by default. If you need rollback capability, add `down()` manually.

## Helper Functions

Add custom helpers in [app/Helpers.php](app/Helpers.php) with `function_exists()` guard:
```php
if (! function_exists('my_helper')) {
    function my_helper(): string { return 'value'; }
}
```

## Architecture Tests (ArchTest.php)

Enforces naming conventions:
- Observers must end with `Observer`
- Policies must end with `Policy`
- Services must end with `Service`
- Casts must end with `Cast`

See [tests/Feature/ArchTest.php](tests/Feature/ArchTest.php).

## Key Files to Reference

- **Filament Resource Pattern:** [ProductResource.php](app/Filament/Resources/ProductResource.php) + subdirectories
- **Complex Resource Logic:** [CreateRawMaterialEntrance.php](app/Filament/Resources/RawMaterialEntranceResource/Pages/CreateRawMaterialEntrance.php) (mutations, transactions)
- **Comprehensive Test Suite:** [UserResourceTest.php](tests/Feature/Filament/Resources/UserResourceTest.php)
- **Enum with Labels:** [MaterialEntranceStatus.php](app/Enums/MaterialEntranceStatus.php)
- **Observer Pattern:** [ProductObserver.php](app/Observers/ProductObserver.php)
- **Global Table Config:** [AppServiceProvider.php](app/Providers/AppServiceProvider.php)

## Essentials Configuration

Review [config/essentials.php](config/essentials.php) for enabled features:
- Strict models (prevents lazy loading, silently discarded attributes)
- Automatic eager loading (reduces N+1 queries)
- Immutable dates (all Carbon instances are CarbonImmutable)
- Prevent stray requests in tests
- Prohibit destructive commands in production

## Common Pitfalls to Avoid

1. **Don't inline Filament form/table configs** - use separate `Schemas/*Form.php` and `Tables/*Table.php` classes
2. **Don't forget `declare(strict_types=1);`** - all PHP files must have this
3. **Don't use `make()` on final classes** - classes are final, use `new` or DI
4. **Don't forget Arabic labels** - all UI text should be in Arabic
5. **Don't skip `RefreshDatabase`** - all tests must use this trait (configured in [Pest.php](tests/Pest.php))
6. **Don't use `dd()` or `dump()`** - ArchTest will fail; use logger or debugbar

## FilamentPHP Skills

For advanced FilamentPHP guidance, reference:
- [.github/skills/filamentphp-planning/SKILL.md](.github/skills/filamentphp-planning/SKILL.md) - Planning and architecture
- [.github/skills/filamentphp-testing/SKILL.md](.github/skills/filamentphp-testing/SKILL.md) - Testing patterns
