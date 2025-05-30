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
        $paidUser = 0;

        $balans = $debitSum - $creditSum;
       // $dd =
        // Якщо власник - користувач, додаємо його зарплату
        if($this->owner_type == 'App\Models\User'){
           $user = User::find($this->owner_id);
              // Перевіряємо, чи користувач має метод production_stages_total
            if ($user && $user->production_stages_total() !== null) {
                $paidUser = $user->production_stages_total();
            }
           $this->balance = $paidUser - $balans;
        }else{
            $this->balance = $balans;
        }
        if($this->owner_type == 'App\Models\Customer'){
            $pay = 0;
            $customer = Customer::find($this->owner_id);
            if ($customer && $customer->calculateOutstandingInvoices() !== null) {
                $paidUser = $customer->calculateOutstandingInvoices();
            }
            // Перевіряємо, чи користувач має метод paidInvoices
            if ($customer && $customer->paidInvoices() !== null) {
                $pay = $customer->paidInvoices();
            }

            $bal = (float)$pay - (float)$paidUser;

            $this->balance = $bal - $balans;// + $paidUser;
            $this->save();
            if($customer->id == 4 ){
                dd($bal, $customer->id, $customer->calculateOutstandingInvoices(), $pay, $paidUser, $balans, $this->balance, $debitSum, $creditSum);
            }
        }

        if($this->owner_type == 'App\Models\Supplier'){
            $supplier = Supplier::find($this->owner_id);
            if ($supplier && $supplier->calculateObligations() !== null) {
                $paidUser = $supplier->calculateObligations();
            }
            $this->balance = $paidUser - $balans;
            $this->save();
            if($supplier->id == 4 ){
              //  dd($supplier->id, $supplier->calculateObligations(), $paidUser, $balans, $this->balance, $debitSum, $creditSum);
            }
        }

////- $paidUser;
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
