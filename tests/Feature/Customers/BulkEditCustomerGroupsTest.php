<?php

namespace Tests\Feature\Customers;

use App\Enum\Customer\CustomerStatus;
use App\Filament\Resources\Customers\Actions\BulkEditCustomerAction;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Service\ServiceType;
use App\Models\Service\ServiceTypeCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BulkEditCustomerGroupsTest extends TestCase
{
    use RefreshDatabase;

    private ServiceType $basic;

    private ServiceType $premium;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basic = ServiceType::create(['name' => 'Basic']);
        $this->premium = ServiceType::create(['name' => 'Premium']);

        ServiceTypeCount::create(['count_value' => 1]);
        ServiceTypeCount::create(['count_value' => 2]);

        $this->customer = Customer::create([
            'name' => 'Test Customer',
            'year_added' => 2024,
            'status' => CustomerStatus::UNPAID,
        ]);

        foreach ([[$this->basic, 1], [$this->basic, 2], [$this->premium, 1], [$this->premium, 2]] as [$type, $count]) {
            CustomerService::create([
                'customer_id' => $this->customer->id,
                'service_type_id' => $type->id,
                'service_count_id' => $count,
                'price' => 10,
                'subscription_start' => 'January 2024',
                'subscription_end' => 'January 2025',
            ]);
        }
    }

    /**
     * @param  array<int, string>  $combinedService
     */
    private function bulkEditSubscriptionEnd(array $combinedService, string $subscriptionEnd): void
    {
        BulkEditCustomerAction::handleAction(
            new Collection([$this->customer->fresh(['services'])]),
            [
                'combined_service' => $combinedService,
                'edit_subscription_end' => true,
                'subscription_end' => $subscriptionEnd,
            ],
        );
    }

    private function endMonthFor(int $typeId, int $countId): string
    {
        return $this->customer->services()
            ->where('service_type_id', $typeId)
            ->where('service_count_id', $countId)
            ->firstOrFail()
            ->subscription_end
            ->format('Y-m');
    }

    public function test_type_group_updates_only_that_types_services(): void
    {
        $this->bulkEditSubscriptionEnd(["type:{$this->basic->id}"], 'March 2025');

        $this->assertSame('2025-03', $this->endMonthFor($this->basic->id, 1));
        $this->assertSame('2025-03', $this->endMonthFor($this->basic->id, 2));

        $this->assertSame('2025-01', $this->endMonthFor($this->premium->id, 1));
        $this->assertSame('2025-01', $this->endMonthFor($this->premium->id, 2));
    }

    public function test_count_group_updates_only_that_counts_services(): void
    {
        $this->bulkEditSubscriptionEnd(['count:1'], 'March 2025');

        $this->assertSame('2025-03', $this->endMonthFor($this->basic->id, 1));
        $this->assertSame('2025-03', $this->endMonthFor($this->premium->id, 1));

        $this->assertSame('2025-01', $this->endMonthFor($this->basic->id, 2));
        $this->assertSame('2025-01', $this->endMonthFor($this->premium->id, 2));
    }

    public function test_mixed_selection_targets_union_and_applies_customer_status(): void
    {
        BulkEditCustomerAction::handleAction(
            new Collection([$this->customer->fresh(['services'])]),
            [
                'combined_service' => ["type:{$this->basic->id}", "{$this->premium->id}-2"],
                'edit_subscription_end' => true,
                'subscription_end' => 'March 2025',
                'edit_status' => true,
                'status' => CustomerStatus::PAID->value,
            ],
        );

        $this->assertSame('2025-03', $this->endMonthFor($this->basic->id, 1));
        $this->assertSame('2025-03', $this->endMonthFor($this->basic->id, 2));
        $this->assertSame('2025-03', $this->endMonthFor($this->premium->id, 2));

        $this->assertSame('2025-01', $this->endMonthFor($this->premium->id, 1));

        $this->assertSame(CustomerStatus::PAID, $this->customer->fresh()->status);
    }

    public function test_empty_service_selection_updates_all_services(): void
    {
        $this->bulkEditSubscriptionEnd([], 'March 2025');

        $this->assertSame('2025-03', $this->endMonthFor($this->basic->id, 1));
        $this->assertSame('2025-03', $this->endMonthFor($this->basic->id, 2));
        $this->assertSame('2025-03', $this->endMonthFor($this->premium->id, 1));
        $this->assertSame('2025-03', $this->endMonthFor($this->premium->id, 2));
    }
}
