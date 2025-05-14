<?php

namespace App\Filament\Resources\NResource\Widgets;

use Filament\Widgets\Widget;


use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Account;

class PaySalaryUserWidget extends Widget
{
    protected static string $view = 'filament.resources.n-resource.widgets.pay-salary-user-widget';


    public $account;

    protected static ?string $pollingInterval = null;


    public function mount($account)
    {
        $this->account = $account;
        //$this->calculate($record);
    }

    protected function getActions(): array
    {
        return [
            Action::make('pay_salary')
                ->label('Виплатити зарплату')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->form([
                    Select::make('account_id')
                        ->label('Рахунок')
                        ->relationship('account', 'name')
                        ->searchable()
                        ->required(),
                    Select::make('user_id')
                        ->label('Співробітник')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    TextInput::make('amount')
                        ->label('Сума')
                        ->numeric()
                        ->required(),
                ])
                ->action(function (array $data): void {

                    dd($data);
                }),
        ];
    }
}
