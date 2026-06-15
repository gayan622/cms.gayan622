<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\RecentOrdersWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int    $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            StatsWidget::class,
            RevenueChartWidget::class,
            RecentOrdersWidget::class,
        ];
    }
}

// ─── Stats overview widget ────────────────────────────────────────────────────


// ─── Revenue chart widget ─────────────────────────────────────────────────────


// ─── Recent orders widget ─────────────────────────────────────────────────────

