<?php

namespace App\Filament\Resources\NResource\Widgets;

use Filament\Widgets\Widget;


use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Account;
use App\Models\User;

class PaySalaryUserWidget extends Widget
{
    protected static string $view = 'filament.resources.n-resource.widgets.pay-salary-user-widget';

    protected int | string | array $columnSpan = 'full';
    //public $isModalOpen = true;

    public $account;


    public function paySalary($userId)
    {
        $account = User::find($userId)->account;

        if ($account) {
            if($account->balance < $account->salary) {
                // Якщо баланс менший за зарплату, не виплачувати
                return;
            }
          //  $account->balance -= $account->salary;
            $account->save();
        }


        // Додайте логіку для виплати зарплати
        // Наприклад, зменшити баланс рахунку на суму зарплати
    }


    //protected static ?string $pollingInterval = null;


    public function mount($account)
    {
        $this->account = $account;
        //$this->calculate($record);
    }

    public function getViewData(): array
    {
        $userId = auth()->id();

        return [
            'account' => $this->account,
            'userId' => $userId,
            'balance' => $this->account->balance,
            'salary' => $this->account->salary,
            'wallets' => Account::where('owner_type', null)->get(),
        ];
    }
}
