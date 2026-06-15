<?php

namespace App\Filament\Widgets;


use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends \Filament\Widgets\ChartWidget
{
    protected static ?string $heading     = 'Revenue (Last 6 months)';
    protected static ?int    $sort        = 2;
    protected static ?string $maxHeight   = '200px';
    public ?string         $dataChecksum = '';

    protected function getData(): array
    {

            $dateGroup = 'DATE_FORMAT(created_at, "%Y-%m")';
            $rows = Order::paid()
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->select([
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as label'),
                DB::raw('SUM(total) as revenue'),
                DB::raw("$dateGroup as sort_key"),
            ])
           ->groupBy(DB::raw('sort_key'), DB::raw('label'))
            ->orderBy('sort_key', 'asc')
            ->get();

        return [
            'datasets' => [[
                'label'           => 'Revenue (LKR)',
                'data'            => $rows->pluck('revenue')->map(fn($v) => round($v, 2))->toArray(),
                'backgroundColor' => 'rgba(201, 168, 76, 0.15)',
                'borderColor'     => '#c9a84c',
                'borderWidth'     => 2,
                'fill'            => true,
                'tension'         => 0.4,
            ]],
            'labels' => $rows->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string { return 'line'; }
}
