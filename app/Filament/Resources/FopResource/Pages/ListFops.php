<?php

namespace App\Filament\Resources\FopResource\Pages;

use App\Filament\Resources\FopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFops extends ListRecords
{
    protected static string $resource = FopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
