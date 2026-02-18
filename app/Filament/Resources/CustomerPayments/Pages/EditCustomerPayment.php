<?php

namespace App\Filament\Resources\CustomerPayments\Pages;

use App\Filament\Resources\CustomerPayments\CustomerPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerPayment extends EditRecord
{
    protected static string $resource = CustomerPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
