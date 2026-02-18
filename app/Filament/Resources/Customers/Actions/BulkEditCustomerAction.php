<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Enum\RoleEnum;
use App\Filament\Resources\Customers\Schemas\CustomerFormFields;
use App\Services\Customers\CustomerService;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkEditCustomerAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('bulkEdit')
            ->authorize(fn () => auth()->user()->role === RoleEnum::ADMIN)
            ->modalHeading('Hromadná zmena zákazníkov')
            ->modalDescription('Vyberte polia, ktoré chcete aby sa nastavili každému zákazníkovi.')
            ->icon('heroicon-s-pencil')
            ->label('Hromadná zmena')
            ->modalSubmitActionLabel('Zmeniť')
            ->action(function (Collection $records, array $data): void {
                if (self::handleAction($records, $data)) {
                    Notification::make()
                        ->success()
                        ->title('Používatelia boli úspešne upravení.')
                        ->send();
                } else {
                    Notification::make()
                        ->danger()
                        ->title('Niekde nastala chyba!')
                        ->body('Nebola vykonaná žiadna zmena.')
                        ->send();
                }
            })
            ->form(self::form());
    }

    public static function form(): array
    {
        return [
            Grid::make([
                'default' => 1,
                'sm' => 3,
                'md' => 6,
                'lg' => 12,
            ])->schema([
                Group::make([
                    Checkbox::make('edit_status')
                        ->label('Status')
                        ->live()
                        ->inline(),
                    CustomerFormFields::status()
                        ->default(null)
                        ->visible(fn (Get $get): bool => $get('edit_status')),
                ])->columnSpan(['default' => 4]),

                Group::make([
                    CustomerFormFields::combinedService(multiple: true)
                        ->required(false)
                        ->visible(fn (Get $get): bool => $get('edit_subscription_start') || $get('edit_subscription_end')),
                    Checkbox::make('edit_subscription_start')
                        ->label('Platnosť od')
                        ->live()
                        ->inline(),
                    CustomerFormFields::subscriptionStart()
                        ->default(null)
                        ->required(false)
                        ->visible(fn (Get $get): bool => $get('edit_subscription_start')),
                    Checkbox::make('edit_subscription_end')
                        ->label('Platnosť do')
                        ->live()
                        ->inline(),
                    CustomerFormFields::subscriptionEnd()
                        ->default(null)
                        ->required(false)
                        ->visible(fn (Get $get): bool => $get('edit_subscription_end')),
                ])->columnSpan(['default' => 8]),
            ]),
        ];
    }

    /**
     * @throws \Throwable
     */
    public static function handleAction(Collection $records, array $data): bool
    {
        $combinedServices = CustomerService::parseMultipleCombinedService($data['combined_service'] ?? null);

        unset($data['combined_service']);

        $editableData = [];
        foreach ($data as $key => $value) {
            if (! str_starts_with($key, 'edit_') && ! empty($data['edit_'.$key])) {
                $editableData[$key] = $value;
            }
        }

        DB::beginTransaction();

        try {
            foreach ($records as $record) {

                if (! empty($combinedServices)) {
                    $customerServices = $record->services->filter(function ($service) use ($combinedServices) {
                        return collect($combinedServices)->contains(function ($combinedService) use ($service) {
                            return $service->service_type_id == $combinedService['service_type_id'] &&
                                $service->service_count_id == $combinedService['service_count_id'];
                        });
                    });
                } else {
                    $customerServices = $record->services;
                }

                foreach ($editableData as $key => $value) {
                    if ($record->isFillable($key)) {
                        $record->$key = $value;
                    } elseif (str_starts_with($key, 'subscription')) {
                        foreach ($customerServices as $service) {
                            $service->update([$key => $value]);
                        }
                    }
                }

                if ($record->isDirty($record->getFillable())) {
                    $record->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::critical('Error updating customers: '.$e->getMessage());
            DB::rollBack();

            return false;
        }

        return true;
    }
}
