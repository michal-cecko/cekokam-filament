<?php

namespace App\Filament\Resources\Servers\Tables;

use App\Filament\Resources\Shared\Filters\CreatedAtFilter;
use App\Filament\Resources\Shared\Filters\IdRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Rozsah'),
                TextColumn::make('server_link')
                    ->label('Link')
                    ->url(fn ($record) => $record->server_link)
                    ->openUrlInNewTab(),
                TextColumn::make('ip_link')
                    ->label('Ip link'),
                TextColumn::make('iban')
                    ->label('IBAN')
                    ->searchable()
                    ->sortable(),
                ColorColumn::make('color')
                    ->label('Farba'),
            ])
            ->filters([
                IdRangeFilter::make(),
                CreatedAtFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->paginated([25, 50, 100, 'all'])
            ->striped()
            ->recordAction('edit')
            ->defaultPaginationPageOption(25)
            ->defaultSort('id', 'desc');
    }
}
