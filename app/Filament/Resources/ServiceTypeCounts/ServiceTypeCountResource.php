<?php

namespace App\Filament\Resources\ServiceTypeCounts;

use App\Filament\Resources\ServiceTypeCounts\Pages\CreateServiceTypeCount;
use App\Filament\Resources\ServiceTypeCounts\Pages\EditServiceTypeCount;
use App\Filament\Resources\ServiceTypeCounts\Pages\ListServiceTypeCounts;
use App\Filament\Resources\ServiceTypeCounts\Schemas\ServiceTypeCountForm;
use App\Filament\Resources\ServiceTypeCounts\Tables\ServiceTypeCountsTable;
use App\Models\Service\ServiceTypeCount;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ServiceTypeCountResource extends Resource
{
    protected static ?string $model = ServiceTypeCount::class;

    protected static ?string $modelLabel = 'Počet služby';

    protected static ?string $pluralModelLabel = 'Počty služieb';

    protected static ?string $recordTitleAttribute = 'count_value';

    protected static UnitEnum|string|null $navigationGroup = 'Služby';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return ServiceTypeCountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceTypeCountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceTypeCounts::route('/'),
            'create' => CreateServiceTypeCount::route('/create'),
            'edit' => EditServiceTypeCount::route('/{record}/edit'),
        ];
    }
}
