<?php

namespace App\Filament\Resources\ServiceTypePrices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ServiceTypePriceForm
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
                        Select::make('service_type_id')
                            ->label('Služba')
                            ->preload()
                            ->searchable()
                            ->relationship('serviceType', 'name')
                            ->placeholder('Vyberte...')
                            ->disabledOn('edit')
                            ->columnSpan(['default' => 3])
                            ->default(request()->query('viaServiceType') ?? null),
                        Select::make('service_count_id')
                            ->label('Počet zariadení')
                            ->disabledOn('edit')
                            ->preload()
                            ->searchable()
                            ->relationship('serviceTypeCount', 'count_value')
                            ->placeholder('Vyberte...')
                            ->columnSpan(['default' => 2])
                            ->default(request()->query('viaServiceTypeCount') ?? null),
                        TextInput::make('price')
                            ->label('Cena (€)')
                            ->required()
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
