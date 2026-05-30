<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AppSettings;
use App\Filament\Pages\ServicePricesTable;
use App\Filament\Resources\AccountSubscriptions\AccountSubscriptionResource;
use App\Filament\Resources\BankAccounts\BankAccountResource;
use App\Filament\Resources\ChannelStreams\ChannelStreamResource;
use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\CustomerPayments\CustomerPaymentResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Servers\ServerResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MarcelWeidum\Passkeys\PasskeysPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->passwordReset()
            ->plugins([
                PasskeysPlugin::make(),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->collapsibleNavigationGroups(false)
            ->maxContentWidth(Width::Full)
            ->collapsedSidebarWidth(100)
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->items([
                    ...CustomerResource::getNavigationItems(),
                    ...CustomerPaymentResource::getNavigationItems(),
                    ...(ServicePricesTable::canAccess() ? ServicePricesTable::getNavigationItems() : []),
                    ...AccountSubscriptionResource::getNavigationItems(),
                    ...BankAccountResource::getNavigationItems(),
                    ...CityResource::getNavigationItems(),
                    ...ServerResource::getNavigationItems(),
                    ...(ChannelStreamResource::canAccess() ? ChannelStreamResource::getNavigationItems() : []),
                    ...UserResource::getNavigationItems(),
                    ...(AppSettings::canAccess() ? AppSettings::getNavigationItems() : []),
                ]);
            })
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/app.css');
    }
}
