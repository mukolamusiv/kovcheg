<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use App\Filament\Widgets\CountMaterials;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        $data = $this->record->warehouseMaterials()
            ->selectRaw('SUM(quantity * price) as sum, COUNT(*) as count')
            ->first();

        dd($data);

        //dd($this->record->account->balance);
        return [
          //  UserAccaunt::make(array($this->record->account)),
            CountMaterials::make(),
        ];
    }
}
