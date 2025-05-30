<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Notifications\Notification;

class Supplier extends Model
{

    //постачальник
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'description',
    ];

    public function account()
    {
        return $this->morphOne(Account::class, 'owner');
    }


    protected static function booted()
    {
        static::created(function ($customer) {
            $customer->account()->create([
                'name' => 'Рахунок постачальника - ' . $customer->name,
                'description' => 'Фінансовий рахунок клієнта',
                'account_type' => 'пасив',
                'account_category' => 'постачальник',
                'currency' => 'UAH',
                'balance' => 0.00,
            ]);
        });
    }

    public function transactions()
    {
        return $this->hasManyThrough(
            Transaction::class,
            TransactionEntry::class,
            'account_id', // Foreign key on TransactionEntry table
            'id',         // Foreign key on Transaction table
            'account_id', // Local key on Account table
            'transaction_id' // Local key on TransactionEntry table
        );
    }

    public function getBalanceAttribute()
    {
        return $this->account->balance;
    }

    public function BankDetails()
    {
        return $this->hasMany(SupplierBankDetail::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function setBalans()
    {
        $this->account->balance = $this->calculateObligations();
        $this->account->save();
        Notification::make()
            ->title('Баланс оновлено!')
            ->body('Оновлено наші зобовязання перед постачальником ' . $this->name)
            ->success()
            ->icon('heroicon-o-x-circle')
            ->send();
    }

    public function calculateObligations()
    {
        // /**
        //  * Обчислити різницю між загальною сумою заборгованості з рахунків-фактур
        //  * та загальною сумою транзакцій.
        //  *
        //  * @return float Розрахована різниця.
        //  */

        // /**
        //  * Отримати загальну суму поля 'due' з усіх пов'язаних рахунків-фактур.
        //  */
        // $invoicesTotal = $this->invoices()->sum('due');


        // /**
        //  * Обчислити загальну суму поля 'amount' з усіх записів пов'язаних транзакцій.
        //  * - Завантажити всі пов'язані транзакції разом з їх записами.
        //  * - Об'єднати записи з усіх транзакцій в одну колекцію.
        //  * - Підсумувати значення поля 'amount' з усіх записів.
        //  */
        // $transactionsTotal = $this->transactions()
        //     ->with('entries') // Завантажити пов'язані записи 'entries' для кожної транзакції.
        //     ->get() // Отримати всі транзакції як колекцію.
        //     ->flatMap(function ($transaction) {
        //     // Витягнути та об'єднати всі записи з кожної транзакції в одну колекцію.
        //     return $transaction->entries;
        //     })
        //     ->sum('amount'); // Обчислити загальну суму поля 'amount' з усіх записів.

        // /**
        //  * Повернути різницю між загальною сумою заборгованості з рахунків-фактур
        //  * та загальною сумою транзакцій.
        //  */
        // return $invoicesTotal - $transactionsTotal;
        $invoicesTotal = $this->invoices()->sum('due');
        $invoicesPaid = $this->invoices()->sum('paid');
        $transactionsTotal = $this->transactions()
            ->with('entries')
            ->get()
            ->flatMap(function ($transaction) {
                return $transaction->entries;
            })
            ->sum('amount');
            if($this->id == 4) {
                //dd($invoicesPaid, $transactionsTotal, $invoicesTotal);
                dd($invoicesPaid, $transactionsTotal, $invoicesTotal);
            }

        return $invoicesPaid - $transactionsTotal + $invoicesTotal;
    }

    public function paidInvoices()
    {
        return $this->invoices()->sum('paid');
    }

}
