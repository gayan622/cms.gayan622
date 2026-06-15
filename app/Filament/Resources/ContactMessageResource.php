<?php

namespace App\Filament\Resources;

use App\Models\ContactMessage;
use Filament\Forms;       // For Forms\Components
use Filament\Forms\Form;  // For the Form type-hint
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use App\Filament\Resources\ContactMessageResource\Pages;

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
