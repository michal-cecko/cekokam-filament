<?php

namespace App\Filament\Resources\ServiceTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ServiceTypeForm
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
                            ->columnSpan(3),
                    ]),
            ]);
    }
}
