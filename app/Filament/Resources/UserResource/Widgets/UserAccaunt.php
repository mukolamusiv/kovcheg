<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Infolists\Components\Actions\Action;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserAccaunt extends BaseWidget
{

    public $account;

    protected static ?string $pollingInterval = null;


    public function mount($account)
    {
        $this->account = $account;
        //$this->calculate($record);
    }

    public function account($account)
    {
        $this->account = $account;
        return $this;
    }


    protected function getStats(): array
    {

        //$balance = $this->record->account->balance;//$this->record->account->balance;

        // foreach ($this->record->account as $account) {
        //     dd();

        //     //$balance = $account->balance;
        // }

        //dd($balance);

      //  dd($this->account->balance);
        return [
           // Stat::make('Total Products', $this->record->production->count()),
                //->icon('heroicon-o-archive'),
            Stat::make('На рахунку ', $this->account->balance),
            Stat::make('Випалатити зарплату ', Action::make('Виплатити зарплату')
                ->url('/transactions/create')
                ->icon('heroicon-o-currency-dollar')),
                //->icon('heroicon-o-shopping-cart'),
            //Stat::make('Total Transactions', $this->record->transactions->count()),
                //->icon('heroicon-o-currency-dollar'),
        ];
    }
}
