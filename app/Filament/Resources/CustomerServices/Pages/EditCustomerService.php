<?php

namespace App\Filament\Resources\CustomerServices\Pages;

use App\Filament\Resources\CustomerServices\CustomerServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerService extends EditRecord
{
    protected static string $resource = CustomerServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
