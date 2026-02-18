<?php

namespace App\Filament\Resources\BankAccounts;

use App\Filament\Resources\BankAccounts\Pages\CreateBankAccount;
use App\Filament\Resources\BankAccounts\Pages\EditBankAccount;
use App\Filament\Resources\BankAccounts\Pages\ListBankAccounts;
use App\Filament\Resources\BankAccounts\RelationManagers\BankAccountCustomerPaymentsRelationManager;
use App\Filament\Resources\BankAccounts\RelationManagers\BankAccountCustomersRelationManager;
use App\Filament\Resources\BankAccounts\Schemas\BankAccountForm;
use App\Filament\Resources\BankAccounts\Tables\BankAccountsTable;
use App\Models\Other\BankAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyEuro;

    protected static ?string $modelLabel = 'Bankový účet';

    protected static ?string $pluralModelLabel = 'Bankové účty';

    protected static ?string $recordTitleAttribute = 'iban';

    public static function form(Schema $schema): Schema
    {
        return BankAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BankAccountCustomersRelationManager::class,
            BankAccountCustomerPaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBankAccounts::route('/'),
            'create' => CreateBankAccount::route('/create'),
            'edit' => EditBankAccount::route('/{record}/edit'),
        ];
    }
}
