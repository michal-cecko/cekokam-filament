<?php

namespace App\Filament\Resources\CustomerPayments\Pages;

use App\Filament\Resources\CustomerPayments\CustomerPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerPayment extends CreateRecord
{
    protected static string $resource = CustomerPaymentResource::class;
}
