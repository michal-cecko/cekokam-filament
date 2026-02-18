<?php

namespace App\Filament\Resources\ChannelStreams\Pages;

use App\Filament\Resources\ChannelStreams\ChannelStreamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChannelStream extends EditRecord
{
    protected static string $resource = ChannelStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
