<?php

namespace App\Filament\Resources\CustomerServices\Tables;

use App\Enum\AccountSubscription\AccountSubscriptionType;
use App\Filament\Resources\Shared\Filters\CreatedAtFilter;
use App\Filament\Resources\Shared\Filters\IdRangeFilter;
use App\Models\Customer\CustomerService;
use App\Models\Service\AccountSubscription;
use App\Services\ServiceType\ServiceTypeService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;

class CustomerServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::formFields())
            ->filters([
                IdRangeFilter::make(),
                CreatedAtFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->paginated([25, 50, 100, 'all'])
            ->striped()
            ->recordAction('edit')
            ->defaultPaginationPageOption(25)
            ->defaultSort('id', 'desc');
    }

    /**
     * Reusable Form Fields for both Resource and Repeater
     */
    public static function formFields(): array
    {
        return [
            'customer_id' => Select::make('customer_id')
                ->label("Zákazník")
                ->searchable()
                ->relationship('customer', 'name')
                ->placeholder("Vyberte...")
                ->columnSpan(['default' => 12, 'md' => 3])
                ->default(request()->query('viaCustomer')),

            'combined_service' => self::getServiceSelectField(),

            'price' => TextInput::make('price')
                ->label("Cena (€)")
                ->numeric()
                ->required()
                ->columnSpan(['default' => 12, 'md' => 2]),

            'subscription_start' => DatePicker::make('subscription_start')
                ->label("Platnosť od")
                ->native(false)
                ->displayFormat('F Y')
                ->required()
                ->columnSpan(['default' => 12, 'md' => 2]),

            'subscription_end' => DatePicker::make('subscription_end')
                ->label("Platnosť do")
                ->native(false)
                ->displayFormat('F Y')
                ->required()
                ->columnSpan(['default' => 12, 'md' => 2]),

            'archive_account_id' => Select::make('archive_account_id')
                ->label("Archív účet")
                ->options(fn() => AccountSubscription::query()->where("type", AccountSubscriptionType::ARCHIVE)->pluck("login", "id"))
                ->placeholder("Vyberte...")
                ->columnSpan(['default' => 12, 'md' => 3])
                ->visible(function ($get) {
                    $archiveID = config("service_types.archive_id");
                    $selected = $get('combined_service');
                    if (!$selected) return false;

                    $selectedServiceTypeID = explode("-", $selected)[0] ?? null;
                    return (string)$archiveID === (string)$selectedServiceTypeID;
                }),

            'continue_next_period' => Checkbox::make('continue_next_period')
                ->label("Pokračuje v ďalšom období")
                ->inline()
                ->default(true)
                ->columnSpanFull(),
        ];
    }

    public static function getServiceSelectField(){
        $services = ServiceTypeService::getGrouppedServiceTypes();

        return Select::make('combined_service')
            ->label("Služba")
            ->searchable()
            ->required()
            ->options($services['options'])
            ->placeholder("Vyberte...")
            ->columnSpan(['default' => 3])
            ->default(fn(mixed $state, ?CustomerService $record) => $record?->getDefaultCombinedService())
            ->afterStateHydrated(function (mixed $state, callable $set, ?CustomerService $record) {
                if (is_null($state) && $record) {
                    $set('combined_service', $record->getDefaultCombinedService());
                }
            })
            ->live()
            ->afterStateUpdated(function ($state, callable $set) use ($services) {
                if ($state && !is_array($state)) {
                    $set('price', ($p = $services['prices'][$state]['price'] ?? null) ? (float)$p : null);
                }
            });
    }
}
