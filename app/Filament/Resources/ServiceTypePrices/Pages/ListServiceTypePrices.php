<?php

namespace App\Filament\Resources\ServiceTypePrices\Pages;

use App\Filament\Resources\ServiceTypePrices\ServiceTypePriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceTypePrices extends ListRecords
{
    protected static string $resource = ServiceTypePriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
