<?php

namespace App\Filament\Resources\AccountSubscriptions\Schemas;

use App\Models\Service\AccountSubscription;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class AccountSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make([
                    'default' => 1,
                    'sm' => 3,
                    'md' => 6,
                    'lg' => 12,
                ])
                    ->schema([
                        TextInput::make('login')
                            ->label('Login')
                            ->required()
                            ->columnSpan([
                                'default' => 12,
                                'sm' => 3,
                                'md' => 3,
                            ]),
                        TextInput::make('expiration_days_to_add')
                            ->label('Vyprší o')
                            ->numeric()
                            ->step(1)
                            ->minValue(1)
                            ->suffix('dní')
                            ->default(fn (?string $state, ?AccountSubscription $record) => $record?->expiry_days)
                            ->afterStateHydrated(function (?string $state, callable $set, ?AccountSubscription $record) {
                                $set('expiration_days_to_add', $record?->expiry_days);
                            })
                            ->columnSpan([
                                'default' => 12,
                                'sm' => 2,
                                'md' => 2,
                            ]),
                        Textarea::make('note')
                            ->label('Poznámky')
                            ->columnSpan(12),
                    ]),
            ]);
    }
}
