<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use App\Filament\Resources\Shared\Filters\CreatedAtFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('iban')
                    ->label('IBAN')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Zákazník')
                    ->searchable(),
                TextColumn::make('note')
                    ->label('Poznámka')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
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
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['customer']));
    }
}
