<?php

namespace App\Filament\Resources\ChannelStreams\Pages;

use App\Filament\Resources\ChannelStreams\ChannelStreamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChannelStreams extends ListRecords
{
    protected static string $resource = ChannelStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
