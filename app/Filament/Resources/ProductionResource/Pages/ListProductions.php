<?php

namespace App\Filament\Resources\ProductionResource\Pages;

use App\Filament\Resources\ProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
class ListProductions extends ListRecords
{
    protected static string $resource = ProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $array = [
            'Створені' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', 'створено')),
            'В роботі' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', 'в роботі')),
            'Виготовлені' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', 'виготовлено')),
            'Скасовані' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', 'скасовано')),
            'Вся продукція' => Tab::make(),
        ];
        return $array;
    }

}
