<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionEntry extends Model
{

    use HasFactory; //SoftDeletes;

    protected $fillable = ['transaction_id', 'account_id', 'entry_type', 'amount'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            //dd($model);

            //dd($model->account);
            // Update account balance
            $account = $model->account;

            // Validate data before saving
            if ($model->amount <= 0) {
                throw new \Exception('Amount must be greater than zero.');
            }

            if (!$account) {
                throw new \Exception("Рахунок не знайдено для транзакції ID: {$model->id}");
            }

            if ($model->entry_type == 'кредит') {
                $account->balance -= $model->amount;
            } elseif ($model->entry_type == 'дебет') {
                $account->balance += $model->amount;
            }

            if(!is_null($account->owner)){
                dd($account->owner_type == 'App\Models\Customer' , $account->owner);
            }

            // if($model->account->owner == Customer::class){
            //     // $account->balance = $account->balance - $model->amount;
            //     // $account->balance = 0;

            // }
            //dd($account->balance);
            $account->save();
        });
    }
}
