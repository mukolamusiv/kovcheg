<?php

namespace App\Filament\Resources\NResource\Widgets;

use Filament\Widgets\Widget;


use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Filament\Notifications\Notification;

class PaySalaryUserWidget extends Widget
{
    protected static string $view = 'filament.resources.n-resource.widgets.pay-salary-user-widget';

    protected int | string | array $columnSpan = 'full';
    //public $isModalOpen = true;

    public $account;
    public $payment;
    public $description;
    public $selectedWallet;
    public $amount;


    public function paySalary($userId)
    {
        $account = User::find($userId)->account;
        $selectedWallet  = Account::find($this->selectedWallet);

        dd($account, $userId, $selectedWallet, $this->amount, $this);

        if ($account) {
            if($account->balance < $account->salary) {
                // Якщо баланс менший за зарплату, не виплачувати
                return;
            }















        // Перевірка, чи сума платежу не менше нуля
        if ($account <= 0) {
            Notification::make()
                ->title('Помилка при проведенні оплати!')
                ->body('Сума платежу повинна бути більше нуля')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;
        }
        // Перевірка, чи рахунок існує
        if (!$account) {
            Notification::make()
                ->title('Помилка при проведенні оплати!')
                ->body('Рахунок не знайдено')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;
        }
        // if($invoice->type == 'продаж'){
        //     $payer = $invoice->customer->account; // ID рахунку клієнта
        //     $receiver = $account; // ID рахунку компанії
        //     $description = 'Оплата клієнтом згідно накладної №'.$invoice->invoice_number; // Опис транзакції
        // }
        // if($invoice->type == 'постачання'){
        //     $payer = $account; // ID рахунку платника
        //     $receiver = $invoice->supplier->account; // ID рахунку постачальника
        //     $description = 'Оплата постачальнику згідно накладної №'.$invoice->invoice_number; // Опис транзакції
        // }
        //dd($payer, $receiver, $description);
        // $transaction = Transaction::makingPaymentUser(
        //     $invoice,
        //     $payer,
        //     $receiver,
        //     $payment,
        //     $description
        // );













            $account->save();
        }


        // Додайте логіку для виплати зарплати
        // Наприклад, зменшити баланс рахунку на суму зарплати
    }


    //protected static ?string $pollingInterval = null;


    public function mount($account)
    {
        //dd($account);
        $this->account = $account;
        $this->amount = $this->account->balance;
        //$this->calculate($record);
    }

    public function getViewData(): array
    {
        //$userId = auth()->id();

        return [
            'account' => $this->account,
            'userId' => $this->account->owner_id,
            'balance' => $this->account->balance,
            'salary' => $this->account->salary,
            'wallets' => Account::where('owner_type', null)->get(),
        ];
    }
}
