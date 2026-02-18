<?php

namespace App\Filament\Resources\Shared\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class IdRangeFilter
{
    public static function make(): Filter
    {
        return Filter::make('id')
            ->form([
                Grid::make(['default' => 2])
                    ->schema([
                        TextInput::make('id_from')
                            ->label('ID od')
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('id_until')
                            ->label('ID do')
                            ->numeric()
                            ->minValue(1),
                    ]),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['id_from'],
                        fn (Builder $query, $date): Builder => $query->where('id', '>=', $date),
                    )
                    ->when(
                        $data['id_until'],
                        fn (Builder $query, $date): Builder => $query->where('id', '<=', $date),
                    );
            });
    }
}
