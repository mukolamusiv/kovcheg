<?php

namespace App\Filament\Resources\TemplateProductionResource\Pages;

use App\Filament\Resources\TemplateProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemplateProductions extends ListRecords
{
    protected static string $resource = TemplateProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
