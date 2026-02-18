<?php

namespace App\Filament\Resources\CustomerServices;

use App\Filament\Resources\CustomerServices\Pages\CreateCustomerService;
use App\Filament\Resources\CustomerServices\Pages\EditCustomerService;
use App\Filament\Resources\CustomerServices\Pages\ListCustomerServices;
use App\Filament\Resources\CustomerServices\Schemas\CustomerServiceForm;
use App\Filament\Resources\CustomerServices\Tables\CustomerServicesTable;
use App\Models\Customer\CustomerService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CustomerServiceResource extends Resource
{
    protected static ?string $model = CustomerService::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'Zákaznícka služba';

    protected static ?string $pluralModelLabel = 'Zákaznícke služby';

    protected static ?string $recordTitleAttribute = 'full_service_name';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return CustomerServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerServicesTable::configure($table);
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
            'index' => ListCustomerServices::route('/'),
            'create' => CreateCustomerService::route('/create'),
            'edit' => EditCustomerService::route('/{record}/edit'),
        ];
    }
}
