<?php

namespace App\Filament\Resources\CustomerPayments\Tables;

use App\Enum\Customer\CustomerPaymentStatus;
use App\Filament\Resources\Shared\Filters\CreatedAtFilter;
use App\Filament\Resources\Shared\Filters\IdRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Meno zákazníka')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('Prijatá suma')
                    ->money('EUR')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('iban')
                    ->label('IBAN')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record): string => CustomerPaymentStatus::colors()[$record->status->value])
                    ->getStateUsing(fn ($record) => CustomerPaymentStatus::translated()[$record->status->value] ?? null)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('received_at')
                    ->label('Dátum prijatia')
                    ->dateTime('d.m.Y - H:i')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Poznámka')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options(CustomerPaymentStatus::translated()),
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
            ->defaultSort('received_at', 'desc');
    }
}
