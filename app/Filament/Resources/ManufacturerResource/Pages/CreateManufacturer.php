<?php
namespace App\Filament\Resources\ManufacturerResource\Pages;
use App\Filament\Resources\ManufacturerResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};


class CreateManufacturer extends CreateRecord {
    protected static string $resource = ManufacturerResource::class;
}

