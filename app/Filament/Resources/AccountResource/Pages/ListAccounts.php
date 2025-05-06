<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Resources\AccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public function getTabs(): array
    {
        $array = [
            'Підприємство' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('owner_type', '=', null)),
            'Працівники' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('owner_type', '=', 'App\Models\User')),
            'Постачальники' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('owner_type', '=', 'App\Models\Supplier')),
            'Клієнти' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('owner_type', '=', 'App\Models\Customer')),
            'Безготівкові' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('account_category', '=', 'банк')),
            'Усі рахунки' => Tab::make(),
        ];
        return $array;
    }
}
