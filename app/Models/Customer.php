<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'address', 'description'];

    public function account()
    {
        return $this->morphOne(Account::class, 'owner');
    }

    protected static function booted()
    {
        static::created(function ($customer) {
            $customer->account()->create([
                'name' => 'Рахунок клієнта - ' . $customer->name,
                'description' => 'Фінансовий рахунок клієнта',
                'account_type' => 'актив',
                'account_category' => 'клієнт',
                'currency' => 'UAH',
                'balance' => 0.00,
            ]);
        });



        // Слухаємо подію "updated" для моделі Customer
        static::updated(function ($customer) {
            // Перераховуємо баланс рахунку клієнта на основі його зобов'язань
            $customer->account->balance = $customer->calculateObligations();
            // Зберігаємо оновлений баланс у базі даних
            $customer->account->save();
        });
        //     $customer->account->balance = $customer->calculateObligations();
        //     $customer->account->save();
        // });
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

    public function size()
    {
        return $this->hasMany(CustomerSize::class);
    }

    public function add_money($amount)
    {
        $this->account->balance += $amount;
        $this->account->save();
    }


    public function calculateOutstandingInvoices()
    {
        $invoicesTotal = $this->invoices()->sum('due');
        $invoicesPaid = $this->invoices()->sum('paid');
        $transactionsTotal = $this->transactions()
            ->with('entries')
            ->get()
            ->flatMap(function ($transaction) {
                return $transaction->entries;
            })
            ->sum('amount');
            // if($this->id == 5) {
            //     //dd($invoicesPaid, $transactionsTotal, $invoicesTotal);
            //     dd($invoicesPaid, $transactionsTotal,$this->transactions, $invoicesTotal);
            // }
        return $invoicesTotal;//$invoicesPaid - $transactionsTotal + $invoicesTotal;
        // Підрахунок суми всіх неоплачених рахунків
        //return $this->invoices()->sum('due');
    }

    public function paidInvoices()
    {
        $invoicesPaid = $this->invoices()->sum('paid');
    }



/**
 * Клас Customer
 *
 * Цей клас представляє модель Customer і використовується для взаємодії
 * з відповідною таблицею бази даних. Він містить методи та властивості,
 * пов'язані з управлінням даними клієнтів.
 *
 * @file /d:/OSPanel/home/kovcheg/app/Models/Customer.php
 */
//
    public function calculateObligations()
    {
        $invoicesTotal = $this->invoices()->sum('due');
        $transactionsTotal = $this->transactions()
            ->with('entries')
            ->get()
            ->flatMap(function ($transaction) {
                return $transaction->entries;
            })
            ->sum('amount');
        return $invoicesTotal - $transactionsTotal;
    }


    public function updateAccount()
    {
        $this->account->balance = $this->calculateObligations();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }
}
