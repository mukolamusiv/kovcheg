<?php

namespace App\Filament\Resources\WarehouseProductionResource\Pages;

use App\Filament\Resources\WarehouseProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWarehouseProductions extends ListRecords
{
    protected static string $resource = WarehouseProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $array = [
            'На складі' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('quantity', '>', 0)),
            'Продані' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('quantity', '=', 0)),
            'Усі' => Tab::make(),
        ];
        return $array;
    }
}
