<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Enum\Customer\CustomerStatus;
use App\Enum\RoleEnum;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use Filament\Support\Enums\Width;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerListStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListCustomers::class;
    }

    public function updatedTableColumnSearches($value): void
    {
        $this->tableColumnSearches = $value ?? [];
    }

    public function updatedTableFilters($value): void
    {
        $this->tableFilters = $value ?? [];
    }

    public static function canView(): bool
    {
        return auth()->user()->role === RoleEnum::ADMIN;
    }

    protected function getStats(): array
    {
        $count = $this->getPageTableQuery()->count();

        // Load active customers once instead of 3 separate queries
        $activeCustomers = $this->getPageTableQuery()
            ->where('status', '!=', CustomerStatus::TURNED_OFF)
            ->with(['services', 'currentPeriodPayments'])
            ->get();

        $unpaid = $activeCustomers->where('status', CustomerStatus::UNPAID)->sum('total_price');
        $all = $activeCustomers->sum('total_price');
        $monthly = $activeCustomers->sum('total_monthly_price');

        return [
            Stat::make('Počet', $count)
                ->icon('heroicon-o-user-group')
                ->maxWidth(Width::Medium)
                ->extraAttributes(['class' => 'stat-info']),

            Stat::make('Nezaplatené', number_format(num: $unpaid, decimal_separator: ',', thousands_separator: ' ').'€')
                ->icon('heroicon-o-currency-euro')
                ->maxWidth(Width::Medium)
                ->extraAttributes(['class' => 'stat-danger']),

            Stat::make('Spolu', number_format(num: $all, decimal_separator: ',', thousands_separator: ' ').'€')
                ->icon('heroicon-o-currency-euro')
                ->maxWidth(Width::Medium)
                ->extraAttributes(['class' => 'stat-success']),

            Stat::make('Mesačne', number_format(num: $monthly, decimal_separator: ',', thousands_separator: ' ').'€')
                ->icon('heroicon-m-calendar-days')
                ->maxWidth(Width::Medium)
                ->extraAttributes(['class' => 'stat-info']),
        ];
    }
}
