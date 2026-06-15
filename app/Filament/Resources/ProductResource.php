<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model           = Product::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()->maxLength(200)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($state, callable $set) =>
                            $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->required()->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('sku')
                        ->required()->unique(ignoreRecord: true)
                        ->default(fn() => 'SKU-' . strtoupper(Str::random(8))),

                    Forms\Components\TextInput::make('short_description')
                        ->required()->maxLength(300)->columnSpanFull(),

                    Forms\Components\RichEditor::make('description')
                        ->columnSpanFull(),
                ])->columns(3),

            Forms\Components\Section::make('Pricing & Stock')
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->required()->numeric()->prefix('LKR')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $get, callable $set) {
                            $sale = $get('sale_price');
                            if ($sale && $state > 0) {
                                $set('discount_percent', round((($state - $sale) / $state) * 100));
                            }
                        }),

                    Forms\Components\TextInput::make('sale_price')
                        ->numeric()->prefix('LKR')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $get, callable $set) {
                            $price = $get('price');
                            if ($state && $price > 0) {
                                $set('discount_percent', round((($price - $state) / $price) * 100));
                            }
                        }),

                    Forms\Components\TextInput::make('discount_percent')
                        ->numeric()->suffix('%')->readOnly()
                        ->helperText('Auto-calculated from price & sale price'),

                    Forms\Components\TextInput::make('stock')
                        ->required()->numeric()->default(0)->minValue(0),

                    Forms\Components\TextInput::make('low_stock_alert')
                        ->numeric()->default(5)
                        ->helperText('Alert when stock drops to this level'),
                ])->columns(3),

            Forms\Components\Section::make('Classification')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->required()->searchable()->preload(),

                    Forms\Components\Select::make('manufacturer_id')
                        ->relationship('manufacturer', 'name')
                        ->searchable()->preload()->nullable(),

                    Forms\Components\TextInput::make('emoji')->default('📦'),

                    Forms\Components\TextInput::make('gradient')
                        ->default('from-violet-500 to-purple-600'),
                ])->columns(2),

            Forms\Components\Section::make('Media')
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->image()->directory('products')->maxSize(4096),

                    Forms\Components\FileUpload::make('images')
                        ->image()->directory('products')->multiple()->maxFiles(8)->maxSize(4096),
                ])->columns(2),

            Forms\Components\Section::make('Visibility')
                ->schema([
                    Forms\Components\Toggle::make('featured')->default(false),
                    Forms\Components\Toggle::make('is_active')->default(true),
                    Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('emoji')->label(''),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('sku')->searchable()->copyable()->color('gray'),
                Tables\Columns\TextColumn::make('category.name')->badge()->color('success'),
                Tables\Columns\TextColumn::make('price')
                    ->money('LKR')->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->money('LKR')->sortable()->label('Sale Price')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('discount_percent')
                    ->suffix('%')->badge()->color('warning')->label('Disc.'),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->color(fn($record) => match (true) {
                        $record->stock === 0           => 'danger',
                        $record->is_low_stock          => 'warning',
                        default                        => 'success',
                    })
                    ->icon(fn($record) => $record->stock === 0 ? 'heroicon-o-x-circle' : null),
                Tables\Columns\IconColumn::make('featured')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('featured'),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn($query) => $query->lowStock())
                    ->label('Low Stock'),
                Tables\Filters\Filter::make('out_of_stock')
                    ->query(fn($query) => $query->where('stock', 0))
                    ->label('Out of Stock'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }

    public static function getNavigationBadge(): ?string
    {
        $low = Product::lowStock()->count();
        return $low > 0 ? (string) $low : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
