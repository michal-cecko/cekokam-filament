<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enum\RoleEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'sm' => 3,
                    'md' => 6,
                    'lg' => 12,
                ])->schema([
                    TextInput::make('name')
                        ->label('Meno a priezvisko')
                        ->required()
                        ->columnSpan(4),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->columnSpan(4)
                        ->hint('Nový účet má vždy heslo "heslo123"'),

                    Select::make('role')
                        ->label('Rola')
                        ->options(RoleEnum::translated())
                        ->required()
                        ->columnSpan(4),
                ]),
            ]);
    }
}
