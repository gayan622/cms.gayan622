<?php

namespace App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListCoupons extends ListRecords {
    protected static string $resource = CouponResource::class;
    protected function getHeaderActions(): array {
        return [Actions\CreateAction::make()];
    }
}
