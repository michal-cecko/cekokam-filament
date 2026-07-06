<?php

namespace Tests\Feature\Customers;

use App\Enum\Customer\CustomerStatus;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Service\ServiceType;
use App\Models\Service\ServiceTypeCount;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SortCustomersBySubscriptionEndTest extends TestCase
{
    use RefreshDatabase;

    private ServiceType $type;

    private ServiceTypeCount $count;

    private Customer $june;

    private Customer $january;

    private Customer $march;

    private Customer $noServices;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());

        $this->type = ServiceType::create(['name' => 'Basic']);
        $this->count = ServiceTypeCount::create(['count_value' => 1]);

        // Same year group; highest_ip order (30, 20, 10, 5) is deliberately the
        // reverse of validity order so the assertions can only pass if the
        // subscription_end sort truly beats the group's default IP ordering.
        $this->june = $this->makeCustomer([10], 'June 2025');
        $this->january = $this->makeCustomer([20], 'January 2025');
        $this->march = $this->makeCustomer([30], 'March 2025');
        $this->noServices = $this->makeCustomer([5], null);
    }

    /**
     * @param  array<int, int>  $ipAddresses
     */
    private function makeCustomer(array $ipAddresses, ?string $subscriptionEnd): Customer
    {
        $customer = Customer::create([
            'name' => 'Customer '.implode('-', $ipAddresses),
            'year_added' => 2024,
            'status' => CustomerStatus::UNPAID,
            'ip_addresses' => $ipAddresses,
        ]);

        if ($subscriptionEnd !== null) {
            CustomerService::create([
                'customer_id' => $customer->id,
                'service_type_id' => $this->type->id,
                'service_count_id' => $this->count->id,
                'price' => 10,
                'subscription_start' => 'January 2024',
                'subscription_end' => $subscriptionEnd,
            ]);
        }

        return $customer;
    }

    public function test_ascending_sort_orders_by_validity_within_the_year_group_with_empty_dates_first(): void
    {
        Livewire::test(ListCustomers::class)
            ->set('tableGrouping', 'year_added')
            ->sortTable('subscription_end', 'asc')
            ->assertCanSeeTableRecords(
                [$this->noServices, $this->january, $this->march, $this->june],
                inOrder: true,
            );
    }

    public function test_descending_sort_keeps_empty_dates_first(): void
    {
        Livewire::test(ListCustomers::class)
            ->set('tableGrouping', 'year_added')
            ->sortTable('subscription_end', 'desc')
            ->assertCanSeeTableRecords(
                [$this->noServices, $this->june, $this->march, $this->january],
                inOrder: true,
            );
    }

    public function test_default_grouped_view_still_orders_each_year_by_ip(): void
    {
        Livewire::test(ListCustomers::class)
            ->set('tableGrouping', 'year_added')
            ->assertCanSeeTableRecords(
                [$this->march, $this->january, $this->june, $this->noServices],
                inOrder: true,
            );
    }
}
