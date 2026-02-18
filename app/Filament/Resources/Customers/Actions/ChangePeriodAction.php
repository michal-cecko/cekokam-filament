<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Enum\Customer\CustomerStatus;
use App\Enum\RoleEnum;
use App\Filament\Resources\Customers\CustomerResource;
use App\Services\Customers\BillingPeriodService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChangePeriodAction
{
    public static function makeBulk(): BulkAction
    {
        $nextPeriod = BillingPeriodService::getCurrentPeriod();

        return BulkAction::make('bulkChangePeriod')
            ->authorize(fn () => auth()->user()->role === RoleEnum::ADMIN)
            ->modalHeading('Preklopenie do obdobia '.$nextPeriod['start']->format('F Y').' - '.$nextPeriod['end']->format('F Y'))
            ->modalDescription('POZOR: Pri preklopení sa nenávratne zmažú služby zákazníkom, ktoré už nepokračujú v ďalšom období. Zákazníci, ktorí majú vypnuté automatické preklápanie, sa preskočia.')
            ->icon('heroicon-s-pencil')
            ->label('Preklopenie')
            ->modalSubmitActionLabel('Preklopiť')
            ->action(function (Collection $records, array $data) use ($nextPeriod): void {
                if (self::handleAction($records, $data)) {
                    Notification::make()
                        ->success()
                        ->title('Používatelia boli úspešne preklopení do obdobia '.$nextPeriod['start']->format('F Y').' - '.$nextPeriod['end']->format('F Y'))
                        ->send();
                } else {
                    Notification::make()
                        ->danger()
                        ->title('Niekde nastala chyba!')
                        ->body('Nebola vykonaná žiadna zmena.')
                        ->send();
                }
            });
    }

    public static function makeSingle(): Action
    {
        $nextPeriod = BillingPeriodService::getCurrentPeriod();

        return Action::make('singleChangePeriod')
            ->authorize(fn () => auth()->user()->role === RoleEnum::ADMIN)
            ->modalHeading('Preklopenie do obdobia '.$nextPeriod['start']->format('F Y').' - '.$nextPeriod['end']->format('F Y'))
            ->modalDescription('POZOR: Pri preklopení sa nenávratne zmažú služby, ktoré už nepokračujú v ďalšom období.')
            ->icon('heroicon-s-pencil')
            ->label('Preklopiť')
            ->modalSubmitActionLabel('Preklopiť')
            ->action(function ($record, array $data) use ($nextPeriod): void {
                if (self::handleAction(collect([$record]), $data, false)) {
                    Notification::make()
                        ->success()
                        ->title('Používateľ bol úspešne preklopený do obdobia '.$nextPeriod['start']->format('F Y').' - '.$nextPeriod['end']->format('F Y'))
                        ->send();

                    redirect(CustomerResource::getUrl('edit', ['record' => $record->id]));
                } else {
                    Notification::make()
                        ->danger()
                        ->title('Niekde nastala chyba!')
                        ->body('Nebola vykonaná žiadna zmena.')
                        ->send();
                }
            })
            ->color('info');
    }

    /**
     * @throws Throwable
     */
    public static function handleAction(Collection $records, array $data, bool $isBulkChange = true): bool
    {
        DB::beginTransaction();

        try {
            foreach ($records as $record) {
                $nextPeriod = BillingPeriodService::getCurrentPeriod();

                if (! $record->can_bulk_change_period && $isBulkChange) {
                    continue;
                }

                $changesWereMadeToCurrentRecord = false;
                foreach ($record->services as $service) {
                    if ($service->continue_next_period) {
                        $currentNextPeriod = $nextPeriod;

                        if ($service->subscription_end > $nextPeriod['end'] || $service->subscription_start > $nextPeriod['start']) {
                            continue;
                        }

                        if ($service->subscription_end > $nextPeriod['start']) {
                            $currentNextPeriod['start'] = $service->subscription_end->copy()->addDay()->startOfMonth();
                        }

                        $service->fill([
                            'subscription_start' => $currentNextPeriod['start'],
                            'subscription_end' => $currentNextPeriod['end'],
                        ]);

                        if ($service->isDirty()) {
                            $changesWereMadeToCurrentRecord = true;
                            $service->save();
                        }
                    } else {
                        $service->delete();
                    }
                }

                if ($changesWereMadeToCurrentRecord && $record->status === CustomerStatus::PAID) {
                    $record->update([
                        'status' => CustomerStatus::UNPAID,
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            Log::critical('Error changing period: '.$e->getMessage());
            DB::rollBack();

            return false;
        }

        return true;
    }
}
