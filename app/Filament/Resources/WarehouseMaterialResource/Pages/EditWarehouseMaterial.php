<?php

namespace App\Filament\Resources\WarehouseMaterialResource\Pages;

use App\Filament\Resources\WarehouseMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseMaterial extends EditRecord
{
    protected static string $resource = WarehouseMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
