<?php namespace App\Filament\Resources;

// ─── CategoryResource ─────────────────────────────────────────────────────────

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms; use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables; use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model           = Category::class;
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int    $navigationSort  = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->live(onBlur: true)
                ->afterStateUpdated(fn($s, callable $set) => $set('slug', \Str::slug($s))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description'),
            Forms\Components\FileUpload::make('image')->image()->directory('categories'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('products_count')->counts('products')->label('Products'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])->reorderable('sort_order')
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}

// ─── ManufacturerResource ─────────────────────────────────────────────────────

namespace App\Filament\Resources;
use App\Models\Manufacturer;

class ManufacturerResource extends Resource
{
    protected static ?string $model           = Manufacturer::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $navigationLabel = 'Manufacturers';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->live(onBlur: true)
                ->afterStateUpdated(fn($s, callable $set) => $set('slug', \Str::slug($s))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('website')->url()->prefix('https://'),
            Forms\Components\FileUpload::make('logo')->image()->directory('manufacturers'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('website')->url()->toggleable(),
            Tables\Columns\TextColumn::make('products_count')->counts('products')->label('Products'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListManufacturers::route('/'),
            'create' => Pages\CreateManufacturer::route('/create'),
            'edit'   => Pages\EditManufacturer::route('/{record}/edit'),
        ];
    }
}

// ─── ContactMessageResource ───────────────────────────────────────────────────

namespace App\Filament\Resources;
use App\Models\ContactMessage;

class ContactMessageResource extends Resource
{
    protected static ?string $model           = ContactMessage::class;
    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $navigationLabel = 'Messages';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->disabled(),
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('subject')->disabled(),
            Forms\Components\Textarea::make('message')->disabled()->columnSpanFull(),
            Forms\Components\Toggle::make('is_read'),
            Forms\Components\Toggle::make('is_replied'),
            Forms\Components\Textarea::make('reply')->columnSpanFull()
                ->helperText('Your reply (for record-keeping — send separately via email)'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('subject')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('message')->limit(60)->toggleable(),
                Tables\Columns\IconColumn::make('is_read')->boolean()->label('Read'),
                Tables\Columns\IconColumn::make('is_replied')->boolean()->label('Replied'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::where('is_read', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'danger'; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'edit'  => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }
}

// ─── SeoSettingResource ───────────────────────────────────────────────────────

namespace App\Filament\Resources;
use App\Models\SeoSetting;

class SeoSettingResource extends Resource
{
    protected static ?string $model           = SeoSetting::class;
    protected static ?string $navigationIcon  = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $navigationLabel = 'SEO Settings';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('page')
                ->options(['home'=>'Home','about'=>'About','blog'=>'Blog','shop'=>'Shop','contact'=>'Contact'])
                ->required()->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('title')
                ->required()->maxLength(70)
                ->helperText('Recommended: 50–70 characters')
                ->suffixAction(
                    Forms\Components\Actions\Action::make('count')
                        ->label(fn($get) => strlen($get('title') ?? '') . '/70')
                        ->disabled()
                ),

            Forms\Components\Textarea::make('description')
                ->required()->maxLength(160)->rows(3)
                ->helperText('Recommended: 120–160 characters')
                ->columnSpanFull(),

            Forms\Components\Textarea::make('keywords')
                ->rows(2)->columnSpanFull()
                ->helperText('Comma-separated keywords'),

            Forms\Components\FileUpload::make('og_image')
                ->image()->directory('seo')->maxSize(2048)
                ->helperText('Open Graph image — 1200×630px recommended')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('page')->badge()->color('primary')->sortable(),
            Tables\Columns\TextColumn::make('title')->limit(60)->searchable(),
            Tables\Columns\TextColumn::make('description')->limit(80)->toggleable(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSeoSettings::route('/'),
            'create' => Pages\CreateSeoSetting::route('/create'),
            'edit'   => Pages\EditSeoSetting::route('/{record}/edit'),
        ];
    }
}
