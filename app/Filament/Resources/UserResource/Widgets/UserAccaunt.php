<?php

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Forms\Components\Select;
use Filament\Infolists\Components\Actions\Action;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Actions;
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
            Stat::make('Випалатити зарплату ',
                ActionsAction::make('Виплатити зарплату')
                    ->label('Виплатити зарплату')
                    ->action(function (array $data) {
                        // dd($data);
                        // $this->record->account->balance = $this->record->account->balance - 100;
                        // $this->record->account->save();
                        // $this->record->account->refresh();
                        // dd($this->record);
                    })
                    ->form([
                        Select::make('account_id')
                            ->label('Виплатити з рахунку')
                            ->options($this->account->where('owner_type', '=', null)->pluck('name', 'id'))
                            ->required()
                            ->default($this->account->id),
                    ])
                    // ->action(function (array $data) {
                    //     dd($data);
                    //     // $this->record->account->balance = $this->record->account->balance - 100;
                    //     // $this->record->account->save();
                    //     // $this->record->account->refresh();
                    //     // dd($this->record);
                    // })
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                ),
                //->icon('heroicon-o-shopping-cart'),
            //Stat::make('Total Transactions', $this->record->transactions->count()),
                //->icon('heroicon-o-currency-dollar'),
        ];
    }
}
