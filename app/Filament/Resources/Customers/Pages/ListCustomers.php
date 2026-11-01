<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Enum\Customer\CustomerStatus;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Service\ServiceType;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ListCustomers extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return CustomerResource::getWidgets();
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Všetci'),
        ];

        foreach (CustomerStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make(CustomerStatus::translated()[$status->value])->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        foreach (Cache::remember('service_types_all', 300, fn () => ServiceType::all()) as $serviceType) {
            $tabs[$serviceType->id] = Tab::make($serviceType->name)->modifyQueryUsing(fn (Builder $query) => $query->whereHas('services', function ($query) use ($serviceType) {
                $query->where('service_type_id', $serviceType->id);
            }));
        }

        return $tabs;
    }

    /**
     * The stats widget does not poll, so it refreshes after a toggle or an action changes customers.
     */
    public function updateTableColumnState(string $column, string $record, mixed $input): mixed
    {
        $result = parent::updateTableColumnState($column, $record, $input);

        $this->dispatch('refresh-customer-stats');

        return $result;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function callMountedAction(array $arguments = []): mixed
    {
        $result = parent::callMountedAction($arguments);

        $this->dispatch('refresh-customer-stats');

        return $result;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
