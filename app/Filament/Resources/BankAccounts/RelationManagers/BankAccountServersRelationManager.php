<?php

namespace App\Filament\Resources\BankAccounts\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BankAccountServersRelationManager extends RelationManager
{
    protected static string $relationship = 'servers';

    protected static ?string $title = 'Servery na tomto účte';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Označenie')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('server_link')
                    ->label('Link')
                    ->url(fn ($record) => $record->server_link)
                    ->openUrlInNewTab(),
                ColorColumn::make('color')
                    ->label('Farba'),
            ]);
    }
}
