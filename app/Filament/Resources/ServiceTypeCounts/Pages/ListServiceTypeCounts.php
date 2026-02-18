<?php

namespace App\Filament\Resources\ServiceTypeCounts\Pages;

use App\Filament\Resources\ServiceTypeCounts\ServiceTypeCountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceTypeCounts extends ListRecords
{
    protected static string $resource = ServiceTypeCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
