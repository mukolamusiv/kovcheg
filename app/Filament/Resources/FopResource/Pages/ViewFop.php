<?php

namespace App\Filament\Resources\FopResource\Pages;

use App\Filament\Resources\FopResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFop extends ViewRecord
{
    protected static string $resource = FopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
