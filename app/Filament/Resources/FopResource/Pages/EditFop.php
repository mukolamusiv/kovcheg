<?php

namespace App\Filament\Resources\FopResource\Pages;

use App\Filament\Resources\FopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFop extends EditRecord
{
    protected static string $resource = FopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
