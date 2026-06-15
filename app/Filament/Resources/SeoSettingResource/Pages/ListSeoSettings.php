<?php

namespace App\Filament\Resources\SeoSettingResource\Pages;
use App\Filament\Resources\SeoSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListSeoSettings extends ListRecords {
    protected static string $resource = SeoSettingResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
