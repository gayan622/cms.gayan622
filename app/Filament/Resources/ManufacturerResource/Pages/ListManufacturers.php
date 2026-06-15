<?php
namespace App\Filament\Resources\ManufacturerResource\Pages;
use App\Filament\Resources\ManufacturerResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListManufacturers extends ListRecords {
    protected static string $resource = ManufacturerResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
