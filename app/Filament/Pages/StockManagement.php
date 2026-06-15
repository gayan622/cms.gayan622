<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Stock Management';
    protected static ?int    $navigationSort  = 11;
    protected static string  $view            = 'filament.pages.stock-management';

    // ── Table ──────────────────────────────────────────────────────────────────
    public function table(Table $table): Table
    {
        return $table
            ->query(Product::with('category')->withoutGlobalScopes())
            ->columns([
                Tables\Columns\TextColumn::make('emoji')->label(''),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()->sortable()->weight('bold'),

                Tables\Columns\TextColumn::make('sku')
                    ->searchable()->copyable()->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('category.name')
                    ->badge()->color('primary'),

                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->color(fn($record) => match (true) {
                        $record->stock === 0           => 'danger',
                        $record->stock <= $record->low_stock_alert => 'warning',
                        default                        => 'success',
                    })
                    ->icon(fn($record) => match (true) {
                        $record->stock === 0           => 'heroicon-m-x-circle',
                        $record->stock <= $record->low_stock_alert => 'heroicon-m-exclamation-circle',
                        default                        => 'heroicon-m-check-circle',
                    }),

                Tables\Columns\TextColumn::make('low_stock_alert')
                    ->label('Alert At')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('LKR')->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stock_status')
                    ->options([
                        'out'  => 'Out of stock',
                        'low'  => 'Low stock',
                        'ok'   => 'In stock',
                    ])
                    ->query(fn($query, $data) => match($data['value'] ?? null) {
                        'out' => $query->where('stock', 0),
                        'low' => $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock_alert'),
                        'ok'  => $query->whereColumn('stock', '>', 'low_stock_alert'),
                        default => $query,
                    }),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('add_stock')
                    ->label('Add Stock')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity to add')
                            ->numeric()->minValue(1)->required(),
                        Textarea::make('note')
                            ->label('Note (optional)')
                            ->rows(2)->placeholder('e.g. New batch received'),
                    ])
                    ->action(function (Product $record, array $data) {
                        $record->increment('stock', (int) $data['quantity']);
                        Notification::make()
                            ->title("Added {$data['quantity']} units to {$record->name}")
                            ->success()->send();
                    }),

                Tables\Actions\Action::make('remove_stock')
                    ->label('Remove Stock')
                    ->icon('heroicon-m-minus-circle')
                    ->color('warning')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity to remove')
                            ->numeric()->minValue(1)->required(),
                        Textarea::make('note')
                            ->label('Reason (optional)')
                            ->rows(2)->placeholder('e.g. Damaged, returned, correction'),
                    ])
                    ->action(function (Product $record, array $data) {
                        $qty = min((int) $data['quantity'], $record->stock);
                        $record->decrement('stock', $qty);
                        Notification::make()
                            ->title("Removed {$qty} units from {$record->name}")
                            ->warning()->send();
                    }),

                Tables\Actions\Action::make('set_alert')
                    ->label('Set Alert')
                    ->icon('heroicon-m-bell-alert')
                    ->color('gray')
                    ->form([
                        TextInput::make('low_stock_alert')
                            ->label('Alert when stock falls to')
                            ->numeric()->minValue(0)->required()
                            ->default(fn(Product $record) => $record->low_stock_alert),
                    ])
                    ->action(function (Product $record, array $data) {
                        $record->update(['low_stock_alert' => $data['low_stock_alert']]);
                        Notification::make()
                            ->title("Alert threshold updated for {$record->name}")
                            ->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_add_stock')
                    ->label('Add Stock to Selected')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity to add to each')
                            ->numeric()->minValue(1)->required(),
                    ])
                    ->action(function ($records, array $data) {
                        $records->each->increment('stock', (int) $data['quantity']);
                        Notification::make()
                            ->title("Stock updated for {$records->count()} products")
                            ->success()->send();
                    }),
            ])
            ->defaultSort('stock')
            ->poll('30s'); // auto-refresh every 30 seconds
    }

    // ── Header stats ───────────────────────────────────────────────────────────
    public function getStockStats(): array
    {
        return [
            'total'     => Product::count(),
            'in_stock'  => Product::whereColumn('stock', '>', 'low_stock_alert')->count(),
            'low_stock' => Product::where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock_alert')->count(),
            'out_stock' => Product::where('stock', 0)->count(),
        ];
    }
}
