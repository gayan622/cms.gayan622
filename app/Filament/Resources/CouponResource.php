<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model           = Coupon::class;
    protected static ?string $navigationIcon  = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int    $navigationSort  = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Coupon Details')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->required()->maxLength(50)->uppercase()
                        ->helperText('e.g. SAVE20, WELCOME10'),

                    Forms\Components\Select::make('discount_type')
                        ->options(['percent' => 'Percentage (%)', 'fixed' => 'Fixed Amount (LKR)'])
                        ->required()->default('percent'),

                    Forms\Components\TextInput::make('discount_value')
                        ->required()->numeric()->minValue(0.01)
                        ->helperText('Enter percent (20 = 20%) or fixed LKR amount'),

                    Forms\Components\TextInput::make('min_order')
                        ->numeric()->prefix('LKR')->default(0)
                        ->helperText('Minimum cart total to apply this coupon'),

                    Forms\Components\TextInput::make('max_discount')
                        ->numeric()->prefix('LKR')->nullable()
                        ->helperText('Cap for percentage discounts (optional)'),

                    Forms\Components\Textarea::make('description')
                        ->maxLength(300)->columnSpanFull(),
                ])->columns(3),

            Forms\Components\Section::make('Usage & Validity')
                ->schema([
                    Forms\Components\TextInput::make('max_uses')
                        ->numeric()->nullable()
                        ->helperText('Leave blank for unlimited'),

                    Forms\Components\TextInput::make('per_user_limit')
                        ->numeric()->default(1),

                    Forms\Components\DateTimePicker::make('starts_at')->nullable(),
                    Forms\Components\DateTimePicker::make('expires_at')->nullable(),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()->weight('bold')->badge()->color('warning')->copyable(),
                Tables\Columns\TextColumn::make('discount_type')->badge()
                    ->color(fn($state) => $state === 'percent' ? 'success' : 'info'),
                Tables\Columns\TextColumn::make('discount_value')
                    ->formatStateUsing(fn($state, $record) =>
                        $record->discount_type === 'percent'
                            ? "{$state}%"
                            : 'LKR ' . number_format($state, 2)
                    ),
                Tables\Columns\TextColumn::make('used_count')
                    ->label('Used')
                    ->formatStateUsing(fn($state, $record) =>
                        $record->max_uses ? "{$state}/{$record->max_uses}" : (string) $state
                    ),
                Tables\Columns\TextColumn::make('min_order')->money('LKR')->toggleable(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime()->toggleable()
                    ->color(fn($record) => $record->expires_at?->isPast() ? 'danger' : null),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
