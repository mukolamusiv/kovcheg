<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserAccaunt extends BaseWidget
{

    private $user;

    public function mount($user)
    {
        $this->user = $user;
        //$this->calculate($record);
    }


    protected function getStats(): array
    {

        //$balance = $this->record->account->balance;//$this->record->account->balance;

        // foreach ($this->record->account as $account) {
        //     dd();

        //     //$balance = $account->balance;
        // }

        //dd($balance);

        return [
           // Stat::make('Total Products', $this->record->production->count()),
                //->icon('heroicon-o-archive'),
            Stat::make('На рахунку', $this->account->balance)
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
                //->icon('heroicon-o-shopping-cart'),
            //Stat::make('Total Transactions', $this->record->transactions->count()),
                //->icon('heroicon-o-currency-dollar'),
        ];
    }
}
