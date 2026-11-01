<?php

namespace Tests\Feature\Customers;

use App\Enum\Customer\CustomerStatus;
use App\Enum\RoleEnum;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Widgets\CustomerListStats;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Customer\Payment\CustomerPayment;
use App\Models\Service\ServiceType;
use App\Models\Service\ServiceTypeCount;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerListStatsTest extends TestCase
{
    use RefreshDatabase;

    private ServiceType $type;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-15 10:00:00');

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => RoleEnum::ADMIN]));

        $this->type = ServiceType::create(['name' => 'Basic']);
        ServiceTypeCount::create(['count_value' => 1]);
    }

    /**
     * @param  array<int, array{0: float, 1: string, 2: string}>  $services  price, start, end
     * @param  array<int, array{0: float, 1: string}>  $payments  amount, received at
     */
    private function makeCustomer(CustomerStatus $status, array $services, array $payments = []): Customer
    {
        $customer = Customer::create([
            'name' => 'Customer '.$status->value,
            'year_added' => 2024,
            'status' => $status,
        ]);

        foreach ($services as [$price, $start, $end]) {
            CustomerService::create([
                'customer_id' => $customer->id,
                'service_type_id' => $this->type->id,
                'service_count_id' => 1,
                'price' => $price,
                'subscription_start' => $start,
                'subscription_end' => $end,
            ]);
        }

        foreach ($payments as [$amount, $receivedAt]) {
            CustomerPayment::create([
                'customer_name' => $customer->name,
                'customer_id' => $customer->id,
                'amount_paid' => $amount,
                'received_at' => $receivedAt,
            ]);
        }

        return $customer;
    }

    /**
     * @return array<string, string>
     */
    private function statValues(): array
    {
        $widget = Livewire::test(CustomerListStats::class)->instance();

        $stats = (fn (): array => $this->getStats())->call($widget);

        return collect($stats)
            ->mapWithKeys(fn (Stat $stat): array => [(string) $stat->getLabel() => (string) $stat->getValue()])
            ->all();
    }

    public function test_stats_sum_open_amounts_of_active_customers_only(): void
    {
        // 6 x 10 + 12 x 5 = 120, minus 30 paid this period (the January payment is outside it) = 90.
        $this->makeCustomer(
            CustomerStatus::UNPAID,
            [[10, 'July 2026', 'December 2026'], [5, 'January 2026', 'December 2026']],
            [[30, '2026-08-10'], [100, '2026-01-10']],
        );
        // 6 x 8 = 48, minus 40 = 8.
        $this->makeCustomer(CustomerStatus::PAID, [[8, 'July 2026', 'December 2026']], [[40, '2026-07-05']]);
        // 3 x 7 = 21, overpaid by 29, so it counts as 0.
        $this->makeCustomer(CustomerStatus::UNPAID, [[7, 'July 2026', 'September 2026']], [[50, '2026-07-05']]);
        // Turned off customers are counted but left out of the sums.
        $this->makeCustomer(CustomerStatus::TURNED_OFF, [[20, 'July 2026', 'December 2026']]);

        $this->assertSame([
            'Počet' => '4',
            'Nezaplatené' => '90€',
            'Spolu' => '98€',
            'Mesačne' => '30€',
        ], $this->statValues());
    }

    public function test_stats_do_not_poll(): void
    {
        Livewire::test(CustomerListStats::class)->assertDontSeeHtml('wire:poll');
    }

    public function test_stats_refresh_when_the_list_page_asks(): void
    {
        $customer = $this->makeCustomer(CustomerStatus::UNPAID, [[10, 'July 2026', 'December 2026']]);

        $widget = Livewire::test(CustomerListStats::class)->assertSee('60€');

        $customer->update(['status' => CustomerStatus::TURNED_OFF]);

        $widget->dispatch('refresh-customer-stats')->assertDontSee('60€');
    }

    public function test_paid_toggle_refreshes_the_stats(): void
    {
        $customer = $this->makeCustomer(CustomerStatus::UNPAID, [[10, 'July 2026', 'December 2026']]);

        Livewire::test(ListCustomers::class)
            ->call('updateTableColumnState', 'is_paid', (string) $customer->id, true)
            ->assertDispatched('refresh-customer-stats');

        $this->assertSame(CustomerStatus::PAID, $customer->refresh()->status);
    }

    public function test_bulk_action_refreshes_the_stats(): void
    {
        $customer = $this->makeCustomer(CustomerStatus::UNPAID, [[10, 'July 2026', 'December 2026']]);

        Livewire::test(ListCustomers::class)
            ->selectTableRecords([$customer])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertDispatched('refresh-customer-stats');

        $this->assertModelMissing($customer);
    }
}
