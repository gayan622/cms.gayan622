<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoSettingResource\Pages;
use App\Models\SeoSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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

