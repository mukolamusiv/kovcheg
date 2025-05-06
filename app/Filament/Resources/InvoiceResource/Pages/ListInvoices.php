<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public function getTabs(): array
    {
        $array = [
            'Вся продукція' => Tab::make(),
            'Продажі' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'продаж')),
            'Постачання' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'постачання')),
            'Переміщення' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'переміщення')),
            'Повернення' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'повернення')),
            'Списання' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'списання')),
        ];
        return $array;
    }

    // private function setTabs()
    // {
    //     $storage = Warehouse::all();
    //     $array = [
    //         'Вся продукція' => Tab::make(),
    //     ];
    //     foreach ($storage as $warehouse) {
    //         $array[$warehouse->name] = Tab::make()
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('warehouse_id','=', $warehouse->id));
    //     }
    //     return $array;
    // }
}
