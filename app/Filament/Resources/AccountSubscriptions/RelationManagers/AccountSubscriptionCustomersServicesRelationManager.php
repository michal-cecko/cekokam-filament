<?php

namespace App\Filament\Resources\AccountSubscriptions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountSubscriptionCustomersServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'customerServices';

    protected static ?string $title = 'Služby zákazníkov';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_service_name')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Zákazník')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_service_name')
                    ->label('Služba'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
