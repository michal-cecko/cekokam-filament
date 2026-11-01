<?php

namespace Tests\Feature\Customers;

use App\Enum\AccountSubscription\AccountSubscriptionType;
use App\Enum\Customer\CustomerStatus;
use App\Enum\RoleEnum;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\CustomerServices\Schemas\CustomerServiceForm;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Other\City;
use App\Models\Service\AccountSubscription;
use App\Models\Service\ServiceType;
use App\Models\Service\ServiceTypeCount;
use App\Models\Service\ServiceTypePrice;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class EditCustomerFormTest extends TestCase
{
    use RefreshDatabase;

    private ServiceType $basic;

    private ServiceType $archive;

    private City $presov;

    private City $kosice;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => RoleEnum::ADMIN]));

        // The service options are cached in a static property, so a previous test's types would leak in.
        CustomerServiceForm::$services = null;

        $this->basic = ServiceType::create(['name' => 'Basic']);
        $this->archive = ServiceType::create(['name' => 'Archív']);
        config(['service_types.archive_id' => $this->archive->id]);

        foreach ([1, 2] as $count) {
            ServiceTypeCount::create(['count_value' => $count]);
        }

        foreach ([[$this->basic, 1, 10], [$this->basic, 2, 16], [$this->archive, 1, 5]] as [$type, $count, $price]) {
            ServiceTypePrice::create(['service_type_id' => $type->id, 'service_count_id' => $count, 'price' => $price]);
        }

        AccountSubscription::create(['login' => 'archive-login', 'type' => AccountSubscriptionType::ARCHIVE]);

        $this->presov = City::create(['name' => 'Prešov']);
        $this->kosice = City::create(['name' => 'Košice']);

        $this->customer = Customer::create([
            'name' => 'Ján Novák',
            'phone' => ['0900123456'],
            'year_added' => 2024,
            'status' => CustomerStatus::UNPAID,
            'city_id' => $this->presov->id,
            'ip_addresses' => [10],
        ]);

        CustomerService::create([
            'customer_id' => $this->customer->id,
            'service_type_id' => $this->basic->id,
            'service_count_id' => 1,
            'price' => 10,
            'subscription_start' => 'July 2026',
            'subscription_end' => 'December 2026',
        ]);
    }

    private function editPage(): Testable
    {
        return Livewire::test(EditCustomer::class, ['record' => $this->customer->getRouteKey()]);
    }

    private function serviceItemKey(Testable $page): string
    {
        return array_key_first($page->get('data.services'));
    }

    public function test_city_select_does_not_render_every_city(): void
    {
        $this->editPage()
            ->assertSee('Prešov')
            ->assertDontSee('Košice');
    }

    public function test_city_select_finds_cities_by_search(): void
    {
        $page = $this->editPage()
            ->call('callSchemaComponentMethod', 'form.city_id', 'getSearchResultsForJs', ['search' => 'Koš']);

        $this->assertSame(['Košice'], array_column($page->effects['returns'][0], 'label'));
    }

    public function test_city_search_ignores_accents(): void
    {
        $page = $this->editPage()
            ->call('callSchemaComponentMethod', 'form.city_id', 'getSearchResultsForJs', ['search' => 'KOSICE']);

        $this->assertSame(['Košice'], array_column($page->effects['returns'][0], 'label'));
    }

    public function test_saving_keeps_the_chosen_city(): void
    {
        $this->editPage()
            ->fillForm(['city_id' => $this->kosice->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($this->kosice->id, $this->customer->refresh()->city_id);
    }

    public function test_changing_the_service_sets_its_price(): void
    {
        $page = $this->editPage();
        $key = $this->serviceItemKey($page);

        $page->set("data.services.{$key}.combined_service", "{$this->basic->id}-2");

        $this->assertEquals(16, $page->get("data.services.{$key}.price"));
    }

    public function test_changing_the_service_re_renders_only_price_and_archive_account(): void
    {
        $page = $this->editPage();
        $key = $this->serviceItemKey($page);

        $page->set("data.services.{$key}.combined_service", "{$this->archive->id}-1");

        $partials = $page->effects['partials'];

        $this->assertArrayNotHasKey('html', $page->effects);
        $this->assertSame([
            "schema-component::form.services.{$key}.price",
            "schema-component::form.services.{$key}.archive_account_id",
        ], array_keys($partials));
        $this->assertStringContainsString('archive-login', $partials["schema-component::form.services.{$key}.archive_account_id"]);
    }

    public function test_archive_account_field_shows_only_for_the_archive_service(): void
    {
        $page = $this->editPage();
        $key = $this->serviceItemKey($page);

        $page->assertDontSee('archive-login');

        $page->set("data.services.{$key}.combined_service", "{$this->archive->id}-1");
        $this->assertStringContainsString('archive-login', $page->effects['partials']["schema-component::form.services.{$key}.archive_account_id"]);

        $page->set("data.services.{$key}.combined_service", "{$this->basic->id}-1");
        $this->assertStringNotContainsString('archive-login', $page->effects['partials']["schema-component::form.services.{$key}.archive_account_id"]);
    }
}
