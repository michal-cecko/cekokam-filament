<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Enum\Customer\CustomerStatus;
use App\Enum\RoleEnum;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use Filament\Support\Enums\Width;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

#[On('refresh-customer-stats')]
class CustomerListStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

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

        $activeCustomers = $this->getPageTableQuery()
            ->where('status', '!=', CustomerStatus::TURNED_OFF)
            ->select(['customers.id', 'customers.status'])
            ->with([
                'services:id,customer_id,price,subscription_start,subscription_end',
                'currentPeriodPayments:id,customer_id,amount_paid',
            ])
            ->get();

        $unpaid = 0;
        $all = 0;
        $monthly = 0;

        foreach ($activeCustomers as $customer) {
            $totalPrice = $customer->total_price;

            $all += $totalPrice;
            $monthly += $customer->total_monthly_price;

            if ($customer->status === CustomerStatus::UNPAID) {
                $unpaid += $totalPrice;
            }
        }

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
