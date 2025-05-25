<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Widgets\CountMaterials;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function getHeaderWidgets(): array
    {
        $materials = $this->record->materials;




        // $totalQuantity = $materials->sum('quantity');
        // $totalCost = $materials->sum(function ($material) {
        //     return $material->quantity * $material->price;
        // });

        $materials = $this->record->materials()->with('warehouses')->get();

        $childCategories = $this->record->children()->with('materials.warehouses')->get();

        foreach ($childCategories as $childCategory) {
            $materials = $materials->merge($childCategory->materials);
        }

        $totalStock = $materials->sum(function ($material) {
            return $material->warehouses->sum('quantity');
        });

        $totalStockCost = $materials->sum(function ($material) {
            return $material->warehouses->sum(function ($warehouseMaterial) {
                return $warehouseMaterial->quantity * $warehouseMaterial->price;
            });
        });
        // dd([
        //     'totalQuantity' => $totalQuantity,
        //     'totalCost' => $totalCost,
        // ]);

        return [
            CountMaterials::make([
                'sum' => round($totalStockCost, 2),
                'count' => $totalStock,
            ]),
        ];
    }
}
