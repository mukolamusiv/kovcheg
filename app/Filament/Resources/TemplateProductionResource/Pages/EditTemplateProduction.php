<?php

namespace App\Filament\Resources\TemplateProductionResource\Pages;

use App\Filament\Resources\TemplateProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTemplateProduction extends EditRecord
{
    protected static string $resource = TemplateProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
