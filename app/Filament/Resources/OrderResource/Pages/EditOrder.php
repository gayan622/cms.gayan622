<?php
namespace App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, EditRecord};


class EditOrder extends EditRecord {
    protected static string $resource = OrderResource::class;
}
