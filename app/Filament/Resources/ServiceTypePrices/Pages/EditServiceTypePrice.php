<?php

namespace App\Filament\Resources\ServiceTypePrices\Pages;

use App\Filament\Resources\ServiceTypePrices\ServiceTypePriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceTypePrice extends EditRecord
{
    protected static string $resource = ServiceTypePriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
