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

}
