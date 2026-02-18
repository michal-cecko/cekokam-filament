<?php

namespace App\Services\AccountSubscription;

use App\Enum\AccountSubscription\AccountSubscriptionExpiryStatus;
use App\Filament\Resources\AccountSubscriptions\AccountSubscriptionResource;
use App\Models\Service\AccountSubscription;
use App\Notifications\ArchiveAccountExpiredNotification;
use App\Notifications\ArchiveAccountExpiresSoonNotification;
use App\Services\Other\HelpService;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Log;

class AccountSubscriptionService
{
    public static function sendExpiryNotifications(): void
    {
        $accounts = AccountSubscription::all();
        foreach ($accounts as $account) {
            if ($account->expiry_status !== AccountSubscriptionExpiryStatus::OK) {
                if ($account->expiry_status === AccountSubscriptionExpiryStatus::EXPIRED) {
                    HelpService::notifyAdmins(
                        FilamentNotification::make()
                            ->title("Účet {$account->login} expiroval.")
                            ->body("Váš archív účet #{$account->id} ({$account->login}) expiroval pred {$account->expiry_days} dňami.")
                            ->danger()
                            ->actions([
                                Action::make('view')
                                    ->label('Predĺžiť platnosť')
                                    ->button()
                                    ->url(AccountSubscriptionResource::getUrl('edit', ['record' => $account]))
                                    ->markAsRead(),
                            ])
                            ->toDatabase(),
                        new ArchiveAccountExpiredNotification($account)
                    );
                } else {
                    HelpService::notifyAdmins(
                        FilamentNotification::make()
                            ->title("Účet {$account->login} čoskoro expiruje!")
                            ->body("Váš archív účet #{$account->id} ({$account->login}) expiruje o {$account->expiry_days} dní.")
                            ->warning()
                            ->actions([
                                Action::make('view')
                                    ->label('Skontrolovať')
                                    ->button()
                                    ->url(AccountSubscriptionResource::getUrl('edit', ['record' => $account]))
                                    ->markAsRead(),
                            ])
                            ->toDatabase(),
                        new ArchiveAccountExpiresSoonNotification($account)
                    );
                }
                Log::info("CRONJOB: Bola odoslana sprava o expiracii uctu #{$account->id} {$account->login}");
            }
        }
    }
}
