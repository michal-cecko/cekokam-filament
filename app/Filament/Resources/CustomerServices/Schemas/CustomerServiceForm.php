<?php

namespace App\Filament\Resources\CustomerServices\Schemas;

use App\Enum\AccountSubscription\AccountSubscriptionType;
use App\Models\Customer\CustomerService;
use App\Models\Service\AccountSubscription;
use App\Services\ServiceType\ServiceTypeService;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class CustomerServiceForm
{
    public static ?Collection $services = null;

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
                    ->schema(self::formFields()),
            ]);
    }

    public static function formFields(): array {
        return [
            Select::make('customer_id')
                ->label('Zákazník')
                ->searchable()
                ->relationship('customer', 'name')
                ->placeholder('Vyberte...')
                ->columnSpan(['default' => 3])
                ->default(request()->query('viaCustomer') ?? null),
            static::getServiceSelectField(),
            TextInput::make('price')
                ->label('Cena (€)')
                ->required()
                ->columnSpan(['default' => 3]),
            Flatpickr::make('subscription_start')
                ->label('Platnosť od')
                ->monthPicker()
                ->required()
                ->dateFormat('F Y')
                ->columnSpan(['default' => 3]),
            Flatpickr::make('subscription_end')
                ->label('Platnosť do')
                ->monthPicker()
                ->required()
                ->dateFormat('F Y')
                ->columnSpan(['default' => 3]),
            Select::make('archive_account_id')
                ->label('Archív účet')
                ->options(AccountSubscription::where('type', AccountSubscriptionType::ARCHIVE)->pluck('login', 'id'))
                ->placeholder('Vyberte...')
                ->columnSpan(['default' => 3])
                ->default(request()->query('viaAccountSubscription') ?? null)
                ->visible(
                    function ($record, $get) {
                        $archiveID = config('service_types.archive_id');
                        $selectedServiceTypeID = explode('-', $get('combined_service'))[0] ?? null;

                        return $archiveID == $selectedServiceTypeID;
                    }
                ),
            Checkbox::make('continue_next_period')
                ->label('Pokračuje v ďalšom období')
                ->inline()
                ->default(true)
                ->columnSpanFull(),
        ];
    }

    public static function getServiceSelectField(): Select
    {
        if (empty(static::$services)) {
            static::$services = ServiceTypeService::getGrouppedServiceTypes();
        }

        return Select::make('combined_service')
            ->label('Služba')
            ->searchable()
            ->required()
            ->options(static::$services['options'])
            ->placeholder('Vyberte...')
            ->columnSpan(['default' => 3])
            ->default(fn (mixed $state, ?CustomerService $record) => $record?->getDefaultCombinedService())
            ->afterStateHydrated(function (mixed $state, callable $set, ?CustomerService $record) {
                if (is_null($state) && $record) {
                    $set('combined_service', $record->getDefaultCombinedService());
                }
            })
            ->live()
            ->afterStateUpdated(function ($state, callable $set) {
                if ($state && ! is_array($state)) {
                    $set('price', ($p = static::$services['prices'][$state]['price'] ?? null) ? (float) $p : null);
                }
            });
    }
}
