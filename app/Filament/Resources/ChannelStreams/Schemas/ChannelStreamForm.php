<?php

namespace App\Filament\Resources\ChannelStreams\Schemas;

use App\Models\ChannelStream;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ChannelStreamForm
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
                        Toggle::make('is_active')
                            ->label('Je aktívne sťahovanie zdroja?')
                            ->columnSpan(12)
                            ->inline(false),
                        TextInput::make('name')
                            ->label('Názov kanálu')
                            ->required()
                            ->columnSpan([
                                'default' => 12,
                                'sm' => 3,
                                'md' => 2,
                                'lg' => 2,
                            ]),
                        TextInput::make('source')
                            ->label('Zdrojová URL')
                            ->url()
                            ->columnSpan([
                                'default' => 12,
                                'sm' => 3,
                                'md' => 6,
                                'lg' => 5,
                            ]),
                        TextInput::make('stream_url')
                            ->label('Výstupná URL')
                            ->afterStateHydrated(function (?string $state, callable $set, ?ChannelStream $record) {
                                $set('stream_url', $record?->stream_url);
                            })
                            ->disabled()
                            ->columnSpan([
                                'default' => 12,
                                'sm' => 3,
                                'md' => 6,
                                'lg' => 5,
                            ]),
                        FileUpload::make('logo')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('logos')
                            ->columnSpan(12),
                    ]),
            ]);
    }
}
