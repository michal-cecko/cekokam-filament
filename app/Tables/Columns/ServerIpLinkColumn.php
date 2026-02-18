<?php

namespace App\Tables\Columns;

use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Builder;

class ServerIpLinkColumn extends Column
{
    protected string $view = 'tables.columns.server-ip-link';

    public function applySorts(Builder $query, bool $descending = false): Builder
    {
        return $query
            ->leftJoin('servers', 'customers.server_id', '=', 'servers.id')
            ->orderBy('servers.name', $descending ? 'desc' : 'asc')
            ->orderByRaw('(customers.ip_addresses->0)::integer DESC')
            ->select('customers.*');
    }

    public function applySearch(Builder $query, string $search): Builder
    {
        // Search for any element in the `ip_addresses` JSON array that contains the search term
        return $query->whereJsonContainsKey("ip_addresses->{$search}");
    }
}
