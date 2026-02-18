<?php

namespace App\Filament\Resources\CustomerPayments;

use App\Filament\Resources\CustomerPayments\Pages\CreateCustomerPayment;
use App\Filament\Resources\CustomerPayments\Pages\EditCustomerPayment;
use App\Filament\Resources\CustomerPayments\Pages\ListCustomerPayments;
use App\Filament\Resources\CustomerPayments\Schemas\CustomerPaymentForm;
use App\Filament\Resources\CustomerPayments\Tables\CustomerPaymentsTable;
use App\Models\Customer\Payment\CustomerPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerPaymentResource extends Resource
{
    protected static ?string $model = CustomerPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $modelLabel = 'Platba';

    protected static ?string $pluralModelLabel = 'Platby';

    protected static ?string $recordTitleAttribute = 'title';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return CustomerPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerPaymentsTable::configure($table);
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
            'index' => ListCustomerPayments::route('/'),
            'create' => CreateCustomerPayment::route('/create'),
            'edit' => EditCustomerPayment::route('/{record}/edit'),
        ];
    }
}
