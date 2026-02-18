<?php

namespace App\Filament\Resources\ServiceTypeCounts\Pages;

use App\Filament\Resources\ServiceTypeCounts\ServiceTypeCountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceTypeCount extends EditRecord
{
    protected static string $resource = ServiceTypeCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
