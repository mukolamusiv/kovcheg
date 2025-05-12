<?php

namespace App\Filament\Resources\WarehouseProductionResource\Pages;

use App\Filament\Resources\WarehouseProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseProduction extends EditRecord
{
    protected static string $resource = WarehouseProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
