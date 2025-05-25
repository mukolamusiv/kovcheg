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
       $data = $this->record->warehouseMaterials;

        dd($data);

        //dd($this->record->account->balance);
        return [
          //  UserAccaunt::make(array($this->record->account)),
            CountMaterials::make(),
        ];
    }
}
