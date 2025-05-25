<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use App\Filament\Widgets\CountMaterials;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function getHeaderWidgets(): array
    {
        $materials = $this->record->warehouseMaterials;

        $totalQuantity = $materials->sum('quantity');
        $totalCost = $materials->sum(function ($material) {
            return $material->quantity * $material->price;
        });

        // dd([
        //     'totalQuantity' => $totalQuantity,
        //     'totalCost' => $totalCost,
        // ]);

    return [
        CountMaterials::make([
            'sum' => $totalCost,
            'count' => $totalQuantity,
        ]),
    ];
    }
}
