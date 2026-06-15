<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\InvoiceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model           = Order::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Summary')
                ->schema([
                    Forms\Components\TextInput::make('order_number')->disabled(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'    => 'Pending',
                            'processing' => 'Processing',
                            'paid'       => 'Paid',
                            'shipped'    => 'Shipped',
                            'completed'  => 'Completed',
                            'cancelled'  => 'Cancelled',
                            'refunded'   => 'Refunded',
                        ])->required(),
                    Forms\Components\Select::make('payment_status')
                        ->options([
                            'pending'  => 'Pending',
                            'paid'     => 'Paid',
                            'failed'   => 'Failed',
                            'refunded' => 'Refunded',
                        ])->required(),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ])->columns(3),

            Forms\Components\Section::make('Customer')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')->disabled(),
                    Forms\Components\TextInput::make('customer_email')->disabled(),
                    Forms\Components\TextInput::make('customer_phone')->disabled(),
                    Forms\Components\TextInput::make('customer_city')->disabled(),
                ])->columns(2),

            Forms\Components\Section::make('Pricing')
                ->schema([
                    Forms\Components\TextInput::make('subtotal')->prefix('LKR')->disabled(),
                    Forms\Components\TextInput::make('discount_amount')->prefix('LKR')->disabled(),
                    Forms\Components\TextInput::make('total')->prefix('LKR')->disabled(),
                    Forms\Components\TextInput::make('coupon_code')->disabled(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()->sortable()->weight('bold')->copyable(),

                Tables\Columns\TextColumn::make('customer_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer_email')->searchable()->toggleable(),

                Tables\Columns\TextColumn::make('total')
                    ->money('LKR')->sortable()->weight('bold'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger'  => 'failed',
                        'gray'    => 'refunded',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'pending',
                        'warning' => 'processing',
                        'primary' => 'paid',
                        'info'    => 'shipped',
                        'success' => 'completed',
                        'danger'  => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('coupon_code')
                    ->badge()->color('warning')->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()->label('Date'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending', 'processing' => 'Processing',
                        'paid' => 'Paid', 'shipped' => 'Shipped',
                        'completed' => 'Completed', 'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed']),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(fn($query, array $data) => $query
                        ->when($data['from'],  fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']))
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->icon('heroicon-o-document-arrow-down')
                    ->label('Invoice')
                    ->color('gray')
                    ->url(fn(Order $record) => route('api.orders.invoice', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('mark_paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Order $r) => $r->payment_status !== 'paid')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->update(['payment_status' => 'paid', 'status' => 'processing']);
                        Notification::make()->title('Order marked as paid')->success()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['status' => 'completed'])),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('status', 'processing')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
