<?php

namespace App\Filament\Resources\AccountSubscriptions\Tables;

use App\Enum\AccountSubscription\AccountSubscriptionExpiryStatus;
use App\Filament\Resources\Shared\Filters\CreatedAtFilter;
use App\Filament\Resources\Shared\Filters\IdRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('login')
                    ->label('Login')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('expiry_status')
                    ->label('Status')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return AccountSubscriptionExpiryStatus::translated()[$record->expiry_status->value] ?? null;
                    })
                    ->badge()
                    ->color(fn ($record): string => AccountSubscriptionExpiryStatus::colors()[$record->expiry_status->value]),
                TextColumn::make('expiry_days')
                    ->label('Vyprší o')
                    ->getStateUsing(function ($record) {
                        return ($record?->expiry_days ?? '???').' dní';
                    })
                    ->sortable(['expires_at']),
                TextColumn::make('note')
                    ->label('Poznámka')
                    ->sortable()
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
