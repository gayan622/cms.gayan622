<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Orders';
    protected static ?int $sort = 3;

    // MUST BE PUBLIC IN V5
    public int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        // Important: Remove ->limit() here.
        // Filament handles pagination automatically.
        // If you want to limit the initial view, use ->defaultPaginationPageOption(8)
        return Order::query()->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('order_number')
                ->searchable()
                ->weight('bold'),

            TextColumn::make('customer_name')
                ->searchable(),

            TextColumn::make('total')
                ->money('LKR'),

            // Modern V5 Badge Syntax
            TextColumn::make('payment_status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'pending' => 'warning',
                    'failed' => 'danger',
                    default => 'gray',
                }),

            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'gray',
                    'processing' => 'warning',
                    'completed' => 'success',
                    default => 'gray',
                }),

            TextColumn::make('created_at')
                ->dateTime()
                ->label('Date'),
        ];
    }
}
