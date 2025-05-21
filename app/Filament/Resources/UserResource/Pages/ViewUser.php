<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\NResource\Widgets\PaySalaryUserWidget;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\UserAccaunt;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }



    public function getHeaderWidgets(): array
    {

        //dd($this->record->account->balance);
        return [
          //  UserAccaunt::make(array($this->record->account)),
            PaySalaryUserWidget::make(array($this->record->account)),
        ];
    }
}
