<?php

namespace App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};


class CreateCoupon extends CreateRecord {
    protected static string $resource = CouponResource::class;
}
