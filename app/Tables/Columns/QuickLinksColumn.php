<?php

namespace App\Tables\Columns;

use App\Models\Customer\Customer;
use Filament\Tables\Columns\Column;

class QuickLinksColumn extends Column
{
    protected string $view = 'tables.columns.quick-links-column';

    public function getServerLinks(Customer $record): array
    {
        return [
            [
                'label' => 'Oscam',
                'url' => $record->oscam_link,
                'icon' => 'heroicon-o-eye',
            ],
            [
                'label' => 'Web',
                'url' => $record->web_link,
                'icon' => 'heroicon-o-eye',
            ],
        ];
    }
}
