<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserAccaunt extends BaseWidget
{

    private $account;

    public function mount($account)
    {
        $this->account = $account;
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
            Stat::make('На рахунку','asdasd'),
                //->icon('heroicon-o-shopping-cart'),
            //Stat::make('Total Transactions', $this->record->transactions->count()),
                //->icon('heroicon-o-currency-dollar'),
        ];
    }
}
