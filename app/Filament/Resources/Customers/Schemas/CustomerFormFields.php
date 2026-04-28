<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enum\Customer\CustomerStatus;
use App\Filament\Resources\CustomerServices\Schemas\CustomerServiceForm;
use App\Forms\Components\PhoneInput;
use App\Models\Customer\Customer;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;

class CustomerFormFields
{
    public static function name(): TextInput
    {
        return TextInput::make('name')
            ->label('Meno a priezvisko')
            ->required();
    }

    public static function yearAdded(): Select
    {
        return Select::make('year_added')
            ->placeholder('Vyberte')
            ->label('Rok pridania')
            ->options(Customer::years())
            ->required()
            ->default(Customer::defaultYear());
    }

    public static function cityId(): Select
    {
        return Select::make('city_id')
            ->label('Obec')
            ->preload()
            ->relationship('city', 'name')
            ->placeholder('Vyberte...')
            ->default(request()->query('viaCity') ?? null);
    }

    public static function phone(): PhoneInput
    {
        return PhoneInput::make('phone')
            ->label('Telefónne čísla');
    }

    public static function serverId(): Select
    {
        return Select::make('server_id')
            ->label('Rozsah')
            ->preload()
            ->relationship('server', 'name')
            ->placeholder('Rozsah')
            ->default(request()->query('viaServer') ?? null);
    }

    public static function ipStart(): TextInput
    {
        return TextInput::make('ip_start')
            ->label('IP od')
            ->numeric()
            ->maxValue(254)
            ->minValue(1)
            ->rules(['sometimes', 'nullable', 'integer'])
            ->afterStateHydrated(function (?string $state, callable $set, ?Customer $record) {
                if (is_null($state) && $record) {
                    $set('ip_start', $record->lowest_ip);
                }
            });
    }

    public static function ipEnd(): TextInput
    {
        return TextInput::make('ip_end')
            ->label('IP do')
            ->numeric()
            ->maxValue(254)
            ->minValue(1)
            ->rules(['sometimes', 'nullable', 'integer'])
            ->afterStateHydrated(function (?string $state, callable $set, ?Customer $record) {
                if (is_null($state) && $record) {
                    $set('ip_end', $record->highest_ip);
                }
            });
    }

    public static function hasDifferentPrices(): Toggle
    {
        return Toggle::make('has_different_prices')
            ->label('Má iné ceny')
            ->inline();
    }

    public static function status(): ToggleButtons
    {
        return ToggleButtons::make('status')
            ->options(CustomerStatus::translated())
            ->default(CustomerStatus::UNPAID->value)
            ->inline()
            ->icons(CustomerStatus::icons())
            ->colors(CustomerStatus::colors());
    }

    public static function note(): Textarea
    {
        return Textarea::make('note')
            ->label('Poznámky');
    }

    public static function subscriptionStart(): Flatpickr
    {
        return Flatpickr::make('subscription_start')
            ->label('Platnosť od')
            ->monthPicker()
            ->dateFormat('F Y');
    }

    public static function subscriptionEnd(): Flatpickr
    {
        return Flatpickr::make('subscription_end')
            ->label('Platnosť do')
            ->monthPicker()
            ->dateFormat('F Y');
    }

    public static function combinedService(bool $multiple = false): Select
    {
        $field = CustomerServiceForm::getServiceSelectField();

        if ($multiple) {
            $field->multiple();
        }

        return $field;
    }
}
