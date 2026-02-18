<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BankAccountForm
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
                        TextInput::make('iban')
                            ->label('IBAN')
                            ->required()
                            ->mask('SK9999999999999999999999')
                            ->columnSpan(12),
                        Textarea::make('note')
                            ->label('Poznámky')
                            ->columnSpan(12),
                    ]),
            ]);
    }
}
