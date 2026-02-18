<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enum\RoleEnum;
use App\Filament\Resources\CustomerServices\Schemas\CustomerServiceForm;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Osobné údaje')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 3,
                            'md' => 6,
                            'lg' => 12,
                        ])->schema(self::formFields()),
                    ])->columnSpanFull(),

                Section::make('Služby')
                    ->schema([
                        static::getServicesRepeater(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function formFields(): array
    {
        $isReadOnly = auth()->user()->role !== RoleEnum::ADMIN;

        return [
            CustomerFormFields::name()
                ->columnSpan(['default' => 12, 'sm' => 3, 'md' => 3])
                ->disabled($isReadOnly),
            CustomerFormFields::yearAdded()
                ->columnSpan(['default' => 12, 'sm' => 3, 'md' => 2])
                ->disabled($isReadOnly),
            CustomerFormFields::cityId()
                ->disabled($isReadOnly)
                ->columnSpan(['default' => 12, 'sm' => 3, 'md' => 3]),
            CustomerFormFields::phone()
                ->columnSpan(['default' => 12, 'sm' => 3, 'md' => 3]),
            CustomerFormFields::serverId()
                ->disabled($isReadOnly)
                ->columnSpan(['default' => 12, 'sm' => 3, 'md' => 2]),
            CustomerFormFields::ipStart()
                ->disabled($isReadOnly)
                ->columnSpan(['default' => 6, 'sm' => 3, 'md' => 2]),
            CustomerFormFields::ipEnd()
                ->disabled($isReadOnly)
                ->columnSpan(['default' => 6, 'sm' => 3, 'md' => 2]),
            CustomerFormFields::iban()
                ->disabled($isReadOnly)
                ->columnSpan(['default' => 12, 'sm' => 3, 'md' => 4]),
            CustomerFormFields::hasDifferentPrices()
                ->disabled($isReadOnly)
                ->columnSpanFull(),
            CustomerFormFields::status()
                ->disabled($isReadOnly)
                ->columnSpan(12),
            CustomerFormFields::note()
                ->columnSpan(12),
        ];
    }

    protected static function getServicesRepeater(): Repeater
    {
        return Repeater::make('services')
            ->label('Služby zákazníka')
            ->relationship('services')
            ->addActionLabel('Pridať službu zákazníkovi')
            ->columnSpanFull()
            ->schema([
                Hidden::make('id'),
                Grid::make([
                    'default' => 1,
                    'sm' => 3,
                    'md' => 6,
                    'lg' => 12,
                ])
                    ->schema(
                        array_diff_key(CustomerServiceForm::formFields(), ['customer_id' => null])
                    ),
            ]);
    }
}
