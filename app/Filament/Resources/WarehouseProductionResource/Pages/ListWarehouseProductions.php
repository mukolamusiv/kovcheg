<?php

namespace App\Filament\Resources\WarehouseProductionResource\Pages;

use App\Filament\Resources\WarehouseProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseProductions extends ListRecords
{
    protected static string $resource = WarehouseProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
