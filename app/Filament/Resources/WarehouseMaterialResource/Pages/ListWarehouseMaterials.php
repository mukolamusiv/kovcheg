<?php

namespace App\Filament\Resources\WarehouseMaterialResource\Pages;

use App\Filament\Resources\WarehouseMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseMaterials extends ListRecords
{
    protected static string $resource = WarehouseMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
