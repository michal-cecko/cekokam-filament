<?php

namespace App\Filament\Resources\Servers\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ServerForm
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
                        TextInput::make('name')
                            ->label('Označenie')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('server_link')
                            ->label('Link')
                            ->url()
                            ->helperText('Napr. http://172.168.12.1')
                            ->columnSpan(3),
                        TextInput::make('ip_link')
                            ->label('IP link')
                            ->helperText('V texte použite premennú "{IP}" kde chcete vložiť IP adresu zákazníka. Napr. http://172.168.12.{IP}')
                            ->columnSpan(3),
                        ColorPicker::make('color')
                            ->label('Farba')
                            ->columnSpan(3),
                        Select::make('iban')
                            ->label('IBAN')
                            ->relationship('bankAccount', 'iban')
                            ->searchable()
                            ->preload()
                            ->placeholder('Vyberte bankový účet')
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
