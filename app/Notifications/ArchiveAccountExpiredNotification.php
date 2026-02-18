<?php

namespace App\Notifications;

use App\Filament\Resources\AccountSubscriptions\AccountSubscriptionResource;
use App\Models\Service\AccountSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArchiveAccountExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(private AccountSubscription $account) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject("POZOR! Účet {$this->account->login} expiroval.")
            ->greeting('Expirácia účtu')
            ->line("Váš účet {$this->account->login} (s ID #{$this->account->id}) expiroval pred {$this->account?->expiry_days} dňami - {$this->account->expires_at->format('j.n.Y')}.")
            ->action('Otvoriť v administrácii', AccountSubscriptionResource::getUrl('edit', ['record' => $this->account->id]));
    }
}
