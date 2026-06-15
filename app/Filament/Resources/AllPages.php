<?php
// ── ProjectResource Pages ──────────────────────────────────────────────────────
namespace App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListProjects extends ListRecords {
    protected static string $resource = ProjectResource::class;
    protected function getHeaderActions(): array {
        return [Actions\CreateAction::make()];
    }
}
class CreateProject extends CreateRecord {
    protected static string $resource = ProjectResource::class;
}
class EditProject extends EditRecord {
    protected static string $resource = ProjectResource::class;
    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}

// ── ProductResource Pages ──────────────────────────────────────────────────────
namespace App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListProducts extends ListRecords {
    protected static string $resource = ProductResource::class;
    protected function getHeaderActions(): array {
        return [Actions\CreateAction::make()];
    }
}
class CreateProduct extends CreateRecord {
    protected static string $resource = ProductResource::class;
}
class EditProduct extends EditRecord {
    protected static string $resource = ProductResource::class;
    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}

// ── OrderResource Pages ────────────────────────────────────────────────────────
namespace App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, EditRecord};

class ListOrders extends ListRecords {
    protected static string $resource = OrderResource::class;
}
class EditOrder extends EditRecord {
    protected static string $resource = OrderResource::class;
}

// ── CouponResource Pages ───────────────────────────────────────────────────────
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
class CreateCoupon extends CreateRecord {
    protected static string $resource = CouponResource::class;
}
class EditCoupon extends EditRecord {
    protected static string $resource = CouponResource::class;
    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}

// ── CategoryResource Pages ─────────────────────────────────────────────────────
namespace App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListCategories extends ListRecords {
    protected static string $resource = CategoryResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
class CreateCategory extends CreateRecord {
    protected static string $resource = CategoryResource::class;
}
class EditCategory extends EditRecord {
    protected static string $resource = CategoryResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}

// ── ManufacturerResource Pages ─────────────────────────────────────────────────
namespace App\Filament\Resources\ManufacturerResource\Pages;
use App\Filament\Resources\ManufacturerResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListManufacturers extends ListRecords {
    protected static string $resource = ManufacturerResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
class CreateManufacturer extends CreateRecord {
    protected static string $resource = ManufacturerResource::class;
}
class EditManufacturer extends EditRecord {
    protected static string $resource = ManufacturerResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}

// ── ContactMessageResource Pages ───────────────────────────────────────────────
namespace App\Filament\Resources\ContactMessageResource\Pages;
use App\Filament\Resources\ContactMessageResource;
use Filament\Resources\Pages\{ListRecords, EditRecord};

class ListContactMessages extends ListRecords {
    protected static string $resource = ContactMessageResource::class;
}
class EditContactMessage extends EditRecord {
    protected static string $resource = ContactMessageResource::class;
}

// ── SeoSettingResource Pages ───────────────────────────────────────────────────
namespace App\Filament\Resources\SeoSettingResource\Pages;
use App\Filament\Resources\SeoSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListSeoSettings extends ListRecords {
    protected static string $resource = SeoSettingResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
class CreateSeoSetting extends CreateRecord {
    protected static string $resource = SeoSettingResource::class;
}
class EditSeoSetting extends EditRecord {
    protected static string $resource = SeoSettingResource::class;
}
