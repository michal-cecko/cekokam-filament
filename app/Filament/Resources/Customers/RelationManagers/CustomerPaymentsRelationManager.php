<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Platby zákazníka';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('Prijatá suma')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('iban')
                    ->label('IBAN')
                    ->searchable(),
                TextColumn::make('received_at')
                    ->label('Dátum prijatia')
                    ->dateTime('d.m.Y - H:i')
                    ->sortable(),
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
