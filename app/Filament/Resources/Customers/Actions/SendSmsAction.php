<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Enum\Customer\CustomerSmsType;
use App\Enum\RoleEnum;
use App\Forms\Components\TextareaWithSmsCount;
use App\Models\Customer\Customer;
use App\Services\Customers\CustomerSmsService;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendSmsAction
{
    public static function makeBulk(): BulkAction
    {
        return BulkAction::make('bulkSendSms')
            ->modalHeading('Hromadné odoslanie SMS')
            ->authorize(fn () => auth()->user()->role === RoleEnum::ADMIN)
            ->icon('heroicon-s-envelope')
            ->label('Hromadná SMS')
            ->modalSubmitActionLabel('Odoslať SMS')
            ->action(function (Collection $records, array $data): void {
                self::handleAction($records, $data);
            })
            ->form(self::form())
            ->color('info');
    }

    public static function makeSingle(): Action
    {
        return Action::make('singleSendSms')
            ->authorize(fn () => auth()->user()->role === RoleEnum::ADMIN)
            ->modalHeading('Odoslanie SMS zákazníkovi')
            ->icon('heroicon-s-envelope')
            ->label('Odoslať SMS')
            ->modalSubmitActionLabel('Odoslať SMS')
            ->action(function ($record, array $data): void {
                self::handleAction($record, $data);
            })
            ->form(self::form())
            ->color('info');
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
                ToggleButtons::make('sms_type')
                    ->label('Typ SMS správy')
                    ->inline()
                    ->options(CustomerSmsType::translated())
                    ->icons(CustomerSmsType::icons())
                    ->default(CustomerSmsType::CUSTOM)
                    ->columnSpan(['default' => 6])
                    ->live()
                    ->colors(CustomerSmsType::colors()),

                TextareaWithSmsCount::make('content')
                    ->label('Obsah správy')
                    ->helperText('Dĺžka jednej správy bez diakrity je 160 znakov. S diakritikou 67 znakov.')
                    ->live()
                    ->visible(fn (Get $get): bool => $get('sms_type') == CustomerSmsType::CUSTOM)
                    ->columnSpan(['default' => 12])
                    ->required(),
            ]),
        ];
    }

    public static function handleAction(Customer|Collection $records, array $data): void
    {
        try {
            if ($data['sms_type'] === CustomerSmsType::PAYMENT_REQUEST) {
                $smsSentCount = CustomerSmsService::sendPaymentRequestSms($records);

                if ($smsSentCount === 0) {
                    Notification::make()
                        ->title('Nebolo čo odoslať')
                        ->body('Neboli nájdení žiadni zákazníci, ktorí nemajú zaplatené..')
                        ->warning()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Odoslané')
                        ->body("Odoslaných SMS o výzve k platbe do Vašej aplikácie BulkGate: {$smsSentCount}.")
                        ->success()
                        ->send();
                }
            } else {
                $smsSentCount = CustomerSmsService::sendCustomSms($records, $data['content']);

                if ($smsSentCount === 0) {
                    Notification::make()
                        ->title('Nebolo čo odoslať')
                        ->body('Neboli nájdené žiadne čísla, ktorým by sa odoslala SMS.')
                        ->warning()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Odoslané')
                        ->body("Odoslaných SMS do Vašej aplikácie BulkGate: {$smsSentCount}.")
                        ->success()
                        ->send();
                }
            }
        } catch (Exception $e) {
            Log::error(now()->format('Y-m-d H:i:s').' | SMS Error: '.json_encode($e->getMessage()));
            Notification::make()
                ->title('Chyba')
                ->body('Nastala chyba pri odosielaní SMS: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
