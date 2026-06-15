<?php

namespace App\Filament\Resources\SeoSettingResource\Pages;
use App\Filament\Resources\SeoSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};


class CreateSeoSetting extends CreateRecord {
    protected static string $resource = SeoSettingResource::class;
}
