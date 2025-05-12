<?php

namespace App\Filament\Resources\WarehouseProductionResource\Pages;

use App\Filament\Resources\WarehouseProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouseProduction extends ViewRecord
{
    protected static string $resource = WarehouseProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
