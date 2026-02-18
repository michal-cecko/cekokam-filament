<?php

namespace App\Filament\Resources\ServiceTypeCounts\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ServiceTypeCountForm
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
                        TextInput::make('count_value')
                            ->label('Počet')
                            ->required()
                            ->columnSpan(3),
                        ColorPicker::make('color')
                            ->label('Farba')
                            ->columnSpan(3),
                    ]),
            ]);
    }
}
