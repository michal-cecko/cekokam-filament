<?php

namespace App\Filament\Resources\ServiceTypeCounts\Pages;

use App\Filament\Resources\ServiceTypeCounts\ServiceTypeCountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceTypeCount extends CreateRecord
{
    protected static string $resource = ServiceTypeCountResource::class;
}
