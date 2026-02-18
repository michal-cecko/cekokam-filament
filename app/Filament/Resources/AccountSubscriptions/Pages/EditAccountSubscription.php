<?php

namespace App\Filament\Resources\AccountSubscriptions\Pages;

use App\Filament\Resources\AccountSubscriptions\AccountSubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountSubscription extends EditRecord
{
    protected static string $resource = AccountSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
