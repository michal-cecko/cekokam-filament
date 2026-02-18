<?php

namespace App\Filament\Resources\Shared\Filters;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CreatedAtFilter
{
    public static function make(): Filter
    {
        return Filter::make('created_at')
            ->form([
                Grid::make(['default' => 2])
                    ->schema([
                        DateTimePicker::make('created_from')
                            ->label('Vytvorené od')
                            ->native(false),
                        DateTimePicker::make('created_until')
                            ->label('Vytvorené do')
                            ->native(false),
                    ]),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['created_from'],
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                    )
                    ->when(
                        $data['created_until'],
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                    );
            });
    }
}
