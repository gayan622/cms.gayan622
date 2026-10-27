<?php

namespace App\Filament\Resources\MovieResource\Pages;

use App\Filament\Resources\MovieResource;
use App\Models\Movie;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class ListMovies extends ListRecords
{
    protected static string $resource = MovieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Standard create button
            Actions\CreateAction::make(),

            // Your custom TMDB Import/Sync pop-up button
            Actions\Action::make('syncTmdb')
                ->label('Sync From TMDB')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    TextInput::make('tmdb_id')
                        ->label('Enter TMDB Movie ID')
                        ->required()
                        ->numeric()
                ])
                ->action(function (array $data) {
                    $movie = Movie::syncWithTmdb($data['tmdb_id']);

                    if ($movie) {
                        Notification::make()
                            ->title('Success')
                            ->body("Successfully synced: {$movie->title}")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Failed')
                            ->body('Could not sync data from TMDB API. Check your configuration or API key.')
                            ->danger()
                            ->send();
                    }
                })
        ];
    }
}
