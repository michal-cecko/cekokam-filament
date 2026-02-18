<?php

namespace App\Filament\Resources\CustomerPayments\Schemas;

use App\Enum\Customer\CustomerPaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CustomerPaymentForm
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
                        TextInput::make('customer_name')
                            ->label('Meno zákazníka')
                            ->columnSpan(['default' => 3]),
                        Select::make('customer_id')
                            ->required()
                            ->label('Zákazník')
                            ->relationship('customer', 'name')
                            ->columnSpan(['default' => 3]),
                        Select::make('status')
                            ->label('Status')
                            ->options(CustomerPaymentStatus::translated())
                            ->required()
                            ->columnSpan(['default' => 2]),
                        Select::make('iban')
                            ->label('IBAN')
                            ->relationship('bankAccount', 'iban')
                            ->searchable()
                            ->required()
                            ->columnSpan(['default' => 3]),
                    ]),
                Grid::make([
                    'default' => 1,
                    'sm' => 3,
                    'md' => 6,
                    'lg' => 12,
                ])
                    ->schema([
                        TextInput::make('amount_paid')
                            ->numeric()
                            ->label('Zaplatená suma (€)')
                            ->columnSpan(['default' => 2]),
                        TextInput::make('amount_expected')
                            ->numeric()
                            ->label('Očakávaná suma (€)')
                            ->columnSpan(['default' => 2]),
                        DateTimePicker::make('received_at')
                            ->label('Dátum prijatia')
                            ->native(false)
                            ->displayFormat('d.m.Y - H:i')
                            ->columnSpan(['default' => 2]),
                        Textarea::make('note')
                            ->label('Poznámky')
                            ->columnSpan(['default' => 12]),
                    ]),
            ]);
    }
}
