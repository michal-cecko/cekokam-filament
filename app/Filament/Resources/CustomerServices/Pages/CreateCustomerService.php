<?php

namespace App\Filament\Resources\CustomerServices\Pages;

use App\Filament\Resources\CustomerServices\CustomerServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerService extends CreateRecord
{
    protected static string $resource = CustomerServiceResource::class;
}
