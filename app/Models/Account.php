<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'balance', 'account_type'];

    public function owner()
    {
        return $this->morphTo();
    }

    public function transactionEntries()
    {
        return $this->hasMany(TransactionEntry::class);
    }

    public function debitEntries()
    {
        return $this->transactionEntries()->where('entry_type', 'дебет');
    }

    public function creditEntries()
    {
        return $this->transactionEntries()->where('entry_type', 'кредит');
    }


    public function syncBalance()
    {
        $debitSum = $this->debitEntries()->sum('amount');
        $creditSum = $this->creditEntries()->sum('amount');
        $this->balance = $debitSum - $creditSum;
        $this->save();
    }


    //додаємо зоюбовязання наші або клієнта
    public function addObligation($sum)
    {
       $this->balance = $this->balance + $sum;
       $this->save();
    }
    // public function calculateOutstandingInvoices()
    // {
    //     if ($this->owner_type === 'App\Models\Client') {
    //         return $this->owner->invoices()
    //             ->where('is_paid', false)
    //             ->sum('amount');
    //     }
    //     return 0;
    // }

}
