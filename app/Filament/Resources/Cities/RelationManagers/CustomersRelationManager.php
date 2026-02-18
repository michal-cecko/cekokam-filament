<?php

namespace App\Filament\Resources\Cities\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    protected static ?string $title = 'Zákazníci v rámci obce';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedUserGroup;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Meno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('surname')
                    ->label('Priezvisko')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telefón')
                    ->searchable(),
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
