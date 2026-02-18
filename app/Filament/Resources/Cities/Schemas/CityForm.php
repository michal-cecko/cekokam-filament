<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CityForm
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
                            ->label('Názov')
                            ->required()
                            ->columnSpan(12),
                        TextInput::make('postal_code')
                            ->label('PSČ')
                            ->required()
                            ->columnSpan(12),
                    ]),
            ]);
    }
}
