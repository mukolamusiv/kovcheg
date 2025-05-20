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


    public $isModalOpen = false;

    public $account;

    protected static ?string $pollingInterval = null;


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
