<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturerResource\Pages;
use App\Models\Manufacturer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Support\Str;

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
                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
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
            Tables\Columns\TextColumn::make('website')->url(fn ($record) => $record->website)->toggleable(),
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
