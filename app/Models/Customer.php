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
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'owner_id')->where('owner_type', 'App\Models\Customer');
    }

    public function getBalanceAttribute()
    {
        return $this->account->balance;
    }

    public function size()
    {
        return $this->hasMany(CustomerSize::class);
    }

}
