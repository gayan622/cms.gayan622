<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovieResource\Pages;
use App\Models\Movie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class MovieResource extends Resource
{


   protected static ?string $model = Movie::class;

    // 1. Set the icon
    protected static ?string $navigationIcon = 'heroicon-o-film';

    // 2. Assign it to the 'Content' section group
    protected static ?string $navigationGroup = 'Content';

    // 3. Set the sort order.
    // Since 'Projects / Blog' and 'Messages' are already there,
    // setting this to a higher number (like 3) pushes it to the bottom of the 'Content' block.
    protected static ?int $navigationSort = 3;

    /**
     * Defines the form fields for Creating and Editing Movies
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('TMDB Integration & Media')
                    ->schema([
                        TextInput::make('tmdb_id')
                            ->label('TMDB ID')
                            ->numeric(),
                        TextInput::make('video_url')
                            ->label('Direct Video URL')
                            ->url(),
                        TextInput::make('poster_path')
                            ->label('Poster Path URL string'),
                        TextInput::make('backdrop_path')
                            ->label('Backdrop Path URL string'),
                    ])->columns(2),

                Forms\Components\Section::make('Movie Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('release_date'),
                        TextInput::make('runtime')
                            ->numeric()
                            ->suffix('mins'),
                        Select::make('status')
                            ->options([
                                0 => 'Draft / Hidden',
                                1 => 'Active / Visible',
                            ])
                            ->default(1)
                            ->required(),
                        Textarea::make('overview')
                            ->columnSpanFull()
                            ->rows(4),
                    ])->columns(2),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        TextInput::make('vote_average')->numeric()->default(0),
                        TextInput::make('vote_count')->numeric()->default(0),
                        TextInput::make('popularity')->numeric()->default(0),
                    ])->columns(3),
            ]);
    }

    /**
     * Defines the layout for the Movie List Page
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // If storing full TMDB image URLs, this renders the poster directly in the row list
                ImageColumn::make('poster_path')
                    ->label('Poster')
                    ->disk('public') // Adjust disk name or use ->imageUrl() if it's an external absolute link
                    ->circular()
                    ->toggleable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('release_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('vote_average')
                    ->label('Rating')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 7 => 'success',
                        $state >= 5 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => $state === '1' ? 'Active' : 'Draft'),

                TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Draft',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Future hint: You can add ActorRelationManager here later!
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovies::route('/'),
        ];
    }
}
