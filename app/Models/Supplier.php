<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        return $this->hasMany(Transaction::class, 'account_id', 'account_id');
    }

    public function getBalanceAttribute()
    {
        return $this->account->balance;
    }

    public function BankDetails()
    {
        return $this->hasMany(SupplierBankDetail::class);
    }

}
