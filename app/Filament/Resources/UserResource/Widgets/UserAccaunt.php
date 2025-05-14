<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Forms\Components\Select;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Actions as BAction;
use Filament\Actions\Action as FilamentActionsAction;
use Filament\Forms\Components\Actions\Action as ActionsAction;

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
            // Stat::make('Випалатити зарплату ',
            // //BAction::make([
            //         Action::make('pay_salary')
            //             ->label('Виплатити зарплату')
            //             ->color('success')
            //             ->icon('heroicon-o-currency-dollar')
            //             ->requiresConfirmation()
            //             ->form([
            //                 Select::make('account_id')
            //                     ->label('Рахунок')
            //                     ->relationship('account', 'name')
            //                     ->required(),
            //                 Select::make('user_id')
            //                     ->label('Співробітник')
            //                     ->relationship('user', 'name')
            //                     ->required(),
            //                 Select::make('amount')
            //                     ->label('Сума')
            //                     ->required()
            //             ])
            //             ->action(function (array $data) {
            //                 //dd($data);
            //                 $this->record->account->addObligation($data['amount']);
            //                 $this->record->account->syncBalance();
            //                 $this->notify('success', 'Зарплата виплачена');
            //             })
            //    // ])

                //->icon('heroicon-o-shopping-cart'),
            //Stat::make('Total Transactions', $this->record->transactions->count()),
                //->icon('heroicon-o-currency-dollar'),
        ];
    }
}
