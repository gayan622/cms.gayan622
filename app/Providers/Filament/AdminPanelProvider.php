<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ReportsPage;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\CouponResource;
use App\Filament\Resources\ManufacturerResource;
use App\Filament\Resources\MovieResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\SeoSettingResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\MovieAnalyticsOverview;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::hex('#c9a84c'),
                'gray'    => Color::Slate,
            ])
            ->brandName('Gayan622 Admin')
            ->brandLogo(null)
            ->favicon(asset('favicon.ico'))
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Content',
                'Shop',
                'Reports',
                'Settings',
            ])
            // 1. ADD THIS TO AUTO-DISCOVER WIDGETS IN THE WIDGETS FOLDER
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            // 2. ADD THIS TO EXPLICITLY REGISTER YOUR DASHBOARD WIDGETS
            ->widgets([
                \App\Filament\Widgets\StatsWidget::class,
                \App\Filament\Widgets\RevenueChartWidget::class,
                \App\Filament\Widgets\RecentOrdersWidget::class,
                 MovieAnalyticsOverview::class, // ➕ Force register the stats widget here
            ])
            ->pages([
                Dashboard::class,
                ReportsPage::class,
                \App\Filament\Pages\Analytics::class,
                \App\Filament\Pages\MovieViewersReport::class,
            ])
            ->resources([
                ProjectResource::class,
                ContactMessageResource::class,
                ProductResource::class,
                CategoryResource::class,
                ManufacturerResource::class,
                CouponResource::class,
                OrderResource::class,
                SeoSettingResource::class,
                MovieResource::class,
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
            ]);
    }
}
