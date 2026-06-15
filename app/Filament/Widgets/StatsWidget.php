<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;



class StatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $thisMonth  = Carbon::now()->startOfMonth();
        $lastMonth  = Carbon::now()->subMonth()->startOfMonth();

        $revThisMonth = Order::paid()->where('created_at', '>=', $thisMonth)->sum('total');
        $revLastMonth = Order::paid()->whereBetween('created_at', [$lastMonth, $thisMonth])->sum('total');
        $revTrend     = $revLastMonth > 0
            ? round((($revThisMonth - $revLastMonth) / $revLastMonth) * 100, 1)
            : 100;

        return [
            Stat::make('Revenue This Month', 'LKR ' . number_format($revThisMonth, 2))
                ->description(($revTrend >= 0 ? '↑ ' : '↓ ') . abs($revTrend) . '% vs last month')
                ->descriptionColor($revTrend >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Total Orders', Order::paid()->count())
                ->description(Order::paid()->where('created_at', '>=', $thisMonth)->count() . ' this month')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary'),

            Stat::make('Pending Orders', Order::where('status', 'processing')->count())
                ->description('Awaiting fulfilment')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Low Stock Items', Product::lowStock()->count())
                ->description(Product::where('stock', 0)->count() . ' out of stock')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('New Messages', ContactMessage::where('is_read', false)->count())
                ->description('Unread contact messages')
                ->icon('heroicon-o-envelope')
                ->color('info'),

            Stat::make('Total Products', Product::active()->count())
                ->description(Product::active()->where('featured', true)->count() . ' featured')
                ->icon('heroicon-o-shopping-bag')
                ->color('gray'),
        ];
    }
}
