<?php

namespace App\Filament\Resources\AccountSubscriptions\Pages;

use App\Filament\Resources\AccountSubscriptions\AccountSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountSubscriptions extends ListRecords
{
    protected static string $resource = AccountSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
