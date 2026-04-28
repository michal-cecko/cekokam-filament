<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enum\Customer\CustomerStatus;
use App\Filament\Resources\Customers\Actions\BulkEditCustomerAction;
use App\Filament\Resources\Customers\Actions\ChangePeriodAction;
use App\Filament\Resources\Customers\Actions\SendSmsAction;
use App\Filament\Resources\Shared\Filters\CreatedAtFilter;
use App\Filament\Resources\Shared\Filters\IdRangeFilter;
use App\Tables\Columns\QuickLinksColumn;
use App\Tables\Columns\ServerIpLinkColumn;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        $counts = Cache::remember('customers_year_counts', 300, fn () => DB::table('customers')
            ->selectRaw('year_added, COUNT(*) as total')
            ->groupBy('year_added')
            ->get()
        );

        return $table
            ->columns([
                TextColumn::make('name_phone')
                    ->label('Zákazník')
                    ->hiddenFrom('md')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $x = "{$record->server?->name} {$record->lowest_ip}.";
                        if ($record->lowest_ip !== $record->highest_ip) {
                            $x .= " - {$record->highest_ip}.";
                        }
                        $x .= "<br>{$record->name}<br>{$record->city?->name}";
                        if (! in_array($record->status, [CustomerStatus::FREE, CustomerStatus::TURNED_OFF])) {
                            $x .= '<br>do '.date('F Y', strtotime($record->subscription_end))."<br>({$record->total_monthly_price}€/mes) {$record->total_price}€";
                        }

                        return $x;
                    }),
                QuickLinksColumn::make('quick_links')
                    ->label('Odkazy')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('server', fn (Builder $q) => $q->where('iban', 'LIKE', "%$search%"));
                    }),
                ServerIpLinkColumn::make('ip_addresses')
                    ->label('Číslo IP')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('name')
                    ->visibleFrom('md')
                    ->label('Meno')
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('Obec')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('phone_string')
                    ->label('Telefón')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereRaw('EXISTS (SELECT 1 FROM jsonb_array_elements_text(customers.phone) AS element WHERE element ILIKE ?)', ["%{$search}%"]);
                    })
                    ->html()
                    ->copyable()
                    ->copyMessage('Číslo skopírované.')
                    ->copyMessageDuration(1500)
                    ->visibleFrom('md'),
                TextColumn::make('subscription_end')
                    ->label('Platnosť do')
                    ->visibleFrom('md')
                    ->getStateUsing(function ($record) {
                        return $record->subscription_end === null ? '-' : date('F Y', strtotime($record->subscription_end));
                    }),
                TextColumn::make('formatted_total')
                    ->label('Celkom (€)')
                    ->money('EUR')
                    ->visibleFrom('md')
                    ->getStateUsing(function ($record) {
                        if (in_array($record->status, [CustomerStatus::FREE, CustomerStatus::TURNED_OFF]) || $record->subscription_end === null) {
                            return '-';
                        }

                        return "({$record->total_monthly_price}€/mes) {$record->total_price}€";
                    }),
                ToggleColumn::make('is_paid')
                    ->extraAttributes(['class' => 'is_paid-toggle'])
                    ->label('Zaplatené?')
                    ->offColor('danger')
                    ->onColor('success')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark')
                    ->disabled(fn ($record) => in_array($record?->status, [CustomerStatus::FREE, CustomerStatus::TURNED_OFF]))
                    ->getStateUsing(fn ($record) => $record->status === CustomerStatus::PAID),
            ])
            ->filters([
                IdRangeFilter::make(),
                CreatedAtFilter::make(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options(CustomerStatus::translated()),
                TernaryFilter::make('can_bulk_change_period')
                    ->label('Môže sa hromadne preklápať?')
                    ->nullable(),
                TernaryFilter::make('has_different_prices')
                    ->label('Iné ceny?')
                    ->nullable(),
            ])
            ->recordActions([
                Action::make('servicesModal')
                    ->label('Služby')
                    ->color('gray')
                    ->icon('heroicon-m-tv')
                    ->modalHeading(fn ($record) => "Služby zákazníka {$record->name}")
                    ->modalContent(fn ($record) => view('tables.customers.services-info-modal', ['record' => $record->loadMissing('services.serviceType')]))
                    ->modalCancelAction(fn (Action $action) => $action->label('Zavrieť'))
                    ->modalSubmitAction(false),
                ActionGroup::make([])->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    SendSmsAction::makeBulk(),
                    ChangePeriodAction::makeBulk(),
                    BulkEditCustomerAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordClasses(function ($record) {
                return match ($record->status) {
                    CustomerStatus::PAID => 'paid-row',
                    CustomerStatus::UNPAID => 'unpaid-row',
                    CustomerStatus::FREE => 'free-row',
                    default => 'turned_off-row',
                };
            })
            ->groups([
                Group::make('year_added')
                    ->label('Rok pridania')
                    ->orderQueryUsing(function (Builder $query, string $direction) {
                        $hasDirectionParam = isset($_GET['tableGroupingDirection']) ||
                            str_contains($_SERVER['HTTP_REFERER'] ?? '', 'tableGroupingDirection');

                        $finalDirection = $hasDirectionParam ? $direction : 'desc';

                        return $query->orderBy('year_added', $finalDirection)->orderBy('highest_ip', $finalDirection);
                    })
                    ->getTitleFromRecordUsing(fn ($record) => "{$record->year_added} (".($counts->where('year_added', $record->year_added)->first()?->total ?? '???').')')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),
            ])
            ->defaultGroup('year_added')
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->paginated([25, 50, 100, 'all'])
            ->striped()
            ->recordAction('edit')
            ->defaultPaginationPageOption(25)
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['city', 'server', 'services', 'currentPeriodPayments']));
    }
}
