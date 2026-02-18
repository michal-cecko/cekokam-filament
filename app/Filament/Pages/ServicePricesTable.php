<?php

namespace App\Filament\Pages;

use App\Enum\RoleEnum;
use App\Models\Service\ServiceType;
use App\Models\Service\ServiceTypeCount;
use App\Models\Service\ServiceTypePrice;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ServicePricesTable extends Page implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    protected static ?string $title = 'Ceny služieb';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTv;

    protected static ?string $navigationLabel = 'Služby';

    protected string $view = 'filament.admin.pages.service-prices-table';

    protected static ?string $slug = 'service-prices-table';

    public $serviceTypes;

    public $serviceCounts;

    public $prices = [];

    public $newTypeName = '';

    public $newCount = '';

    public static function canAccess(): bool
    {
        return auth()->user()->role === RoleEnum::ADMIN;
    }

    public function mount(): void
    {
        $this->serviceTypes = ServiceType::all();
        $this->serviceCounts = ServiceTypeCount::orderBy('count_value')->get();
        $this->loadPrices();
    }

    public function removeServiceType($typeId): void
    {
        ServiceType::find($typeId)->delete();
        $this->serviceTypes = ServiceType::all();
        $this->loadPrices();

        Notification::make()
            ->title('Service type removed successfully')
            ->success()
            ->send();
    }

    public function removeServiceCount($countId): void
    {
        ServiceTypeCount::find($countId)->delete();
        $this->serviceCounts = ServiceTypeCount::orderBy('count_value')->get();
        $this->loadPrices();

        Notification::make()
            ->title('Service count removed successfully')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addServiceType')
                ->label('Pridať službu')
                ->icon('heroicon-m-plus')
                ->form([
                    TextInput::make('newTypeName')
                        ->label('Názov služby')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    ServiceType::create([
                        'name' => $data['newTypeName'],
                    ]);

                    $this->serviceTypes = ServiceType::all();
                    $this->loadPrices();

                    Notification::make()
                        ->title('Služba bola úspešne pridaná.')
                        ->success()
                        ->send();
                }),

            Action::make('addServiceCount')
                ->label('Pridať počet TV')
                ->icon('heroicon-m-plus')
                ->form([
                    TextInput::make('newCount')
                        ->label('Počet TV')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(100),
                ])
                ->action(function (array $data): void {
                    ServiceTypeCount::create([
                        'count_value' => $data['newCount'],
                    ]);

                    $this->serviceCounts = ServiceTypeCount::orderBy('count_value')->get();
                    $this->loadPrices();

                    Notification::make()
                        ->title('Počet TV bol úspešne pridaný.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function loadPrices(): void
    {
        $servicePrices = ServiceTypePrice::all();
        $this->prices = collect();

        foreach ($this->serviceCounts as $serviceCount) {
            foreach ($this->serviceTypes as $serviceType) {
                $priceEntry = $servicePrices->where('service_count_id', $serviceCount->count_value)->where('service_type_id', $serviceType->id)->first();

                if (empty($this->prices[$serviceCount->count_value])) {
                    $this->prices->put($serviceCount->count_value, collect());
                }

                $this->prices[$serviceCount->count_value]->put($serviceType->id, $priceEntry ? (float)$priceEntry->price : null);
            }
        }
    }

    public function updatePrice($countId, $typeId, $value): void
    {
        $price = ServiceTypePrice::firstOrNew([
            'service_count_id' => $countId,
            'service_type_id' => $typeId,
        ]);

        $price->price = $value;
        $price->save();

        Notification::make()
            ->title('Price updated successfully')
            ->success()
            ->send();
    }
}
