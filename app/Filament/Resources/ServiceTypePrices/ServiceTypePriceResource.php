<?php

namespace App\Filament\Resources\ServiceTypePrices;

use App\Filament\Resources\ServiceTypePrices\Pages\CreateServiceTypePrice;
use App\Filament\Resources\ServiceTypePrices\Pages\EditServiceTypePrice;
use App\Filament\Resources\ServiceTypePrices\Pages\ListServiceTypePrices;
use App\Filament\Resources\ServiceTypePrices\Schemas\ServiceTypePriceForm;
use App\Filament\Resources\ServiceTypePrices\Tables\ServiceTypePricesTable;
use App\Models\Service\ServiceTypePrice;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServiceTypePriceResource extends Resource
{
    protected static ?string $model = ServiceTypePrice::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'Cena služby';

    protected static ?string $pluralModelLabel = 'Ceny služieb';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return ServiceTypePriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceTypePricesTable::configure($table);
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
            'index' => ListServiceTypePrices::route('/'),
            'create' => CreateServiceTypePrice::route('/create'),
            'edit' => EditServiceTypePrice::route('/{record}/edit'),
        ];
    }
}
