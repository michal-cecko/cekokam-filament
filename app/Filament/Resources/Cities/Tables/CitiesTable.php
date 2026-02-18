<?php

namespace App\Filament\Resources\Cities\Tables;

use App\Filament\Resources\Shared\Filters\CreatedAtFilter;
use App\Filament\Resources\Shared\Filters\IdRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Názov')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postal_code')
                    ->label('PSČ')
                    ->searchable(),
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
