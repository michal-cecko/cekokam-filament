# Shared Filament Components

This directory contains shared components used across all Filament resources in this application.

## Directory Structure

```
Shared/
└── Filters/
    ├── IdRangeFilter.php          # Reusable ID range filter
    └── CreatedAtFilter.php        # Reusable date range filter
```

## Table Configuration Pattern

Each resource has its own Table class with a simple `configure()` method containing all table configuration inline:

```php
use App\Filament\Resources\Shared\Filters\CreatedAtFilter;
use App\Filament\Resources\Shared\Filters\IdRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                // ...
            ])
            ->filters([
                IdRangeFilter::make(),
                CreatedAtFilter::make(),
                // custom filters...
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->paginated([25, 50, 100, 'all'])
            ->striped()
            ->recordAction('edit')
            ->defaultPaginationPageOption(25)
            ->defaultSort('id', 'desc');
    }
}
```

## Shared Filters

### IdRangeFilter

Filter records by ID from/to range.

### CreatedAtFilter

Filter records by creation date range.

## Usage in Resources

Resources delegate to their Table classes:

```php
class CustomerResource extends Resource
{
    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }
}
```
