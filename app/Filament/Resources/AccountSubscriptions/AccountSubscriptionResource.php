<?php

namespace App\Filament\Resources\AccountSubscriptions;

use App\Filament\Resources\AccountSubscriptions\Pages\CreateAccountSubscription;
use App\Filament\Resources\AccountSubscriptions\Pages\EditAccountSubscription;
use App\Filament\Resources\AccountSubscriptions\Pages\ListAccountSubscriptions;
use App\Filament\Resources\AccountSubscriptions\RelationManagers\AccountSubscriptionCustomersServicesRelationManager;
use App\Filament\Resources\AccountSubscriptions\Schemas\AccountSubscriptionForm;
use App\Filament\Resources\AccountSubscriptions\Tables\AccountSubscriptionsTable;
use App\Models\Service\AccountSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountSubscriptionResource extends Resource
{
    protected static ?string $model = AccountSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $modelLabel = 'Archív účet';

    protected static ?string $pluralModelLabel = 'Archív účty';

    protected static ?string $recordTitleAttribute = 'login';

    public static function form(Schema $schema): Schema
    {
        return AccountSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountSubscriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AccountSubscriptionCustomersServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountSubscriptions::route('/'),
            'create' => CreateAccountSubscription::route('/create'),
            'edit' => EditAccountSubscription::route('/{record}/edit'),
        ];
    }
}
