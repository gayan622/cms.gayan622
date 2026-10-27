<?php
namespace App\Filament\Resources\ContactMessageResource\Pages;
use App\Filament\Resources\ContactMessageResource;
use Filament\Resources\Pages\{ListRecords, EditRecord};

class ListContactMessages extends ListRecords {
    protected static string $resource = ContactMessageResource::class;
}
