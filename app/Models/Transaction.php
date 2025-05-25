<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Notifications\Notification;

class Transaction extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number', 'description', 'transaction_date', 'status',
        'user_id', 'customer_id', 'supplier_id', 'invoice_id'
    ];

    public function amount(){
        //dd($this->debet);
        return $this->debet->last();
    }

    public function entries()
    {
        return $this->hasMany(TransactionEntry::class);
    }

    public function debet()
    {
        return $this->hasOne(TransactionEntry::class)
                ->where('entry_type', 'кредит')
                ->latestOfMany();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }


    private function generator_number(){
        return 'TXN-' . now()->format('YmdHis') . '-' . uniqid();
    }


    public static function makingPayment(Invoice $invoice, Account $payer, Account $receiver, float $sum, string $description = null)
    {
         // Використовуємо транзакцію для забезпечення цілісності даних
         return (new static)->getConnection()->transaction(function () use ($invoice, $payer, $receiver, $sum, $description) {
            // Перевірка обов'язкових полів
            if (!$invoice || !$payer || !$receiver || !$sum) {
                throw new \InvalidArgumentException('Відсутні обов’язкові поля для створення транзакції.');
            }

            // Створення транзакції
            $transaction = new self();
            $transaction->reference_number = 'TXN-' . now()->format('YmdHis') . '-' . uniqid();
            $transaction->description = $description ?? 'Оплата за накладну: ' . $invoice->invoice_number;
            $transaction->transaction_date = now();
            $transaction->status = 'проведено';
            $transaction->user_id = auth()->id();
            //$transaction->customer_id = $customer->id;
            $transaction->invoice_id = $invoice->id;
            $transaction->save();

            // Створення двох записів TransactionEntry
            $entry1 = new TransactionEntry();
            $entry1->transaction_id = $transaction->id;
            $entry1->account_id = $receiver->id; // ID рахунку для дебету
            $entry1->entry_type = 'дебет';
            $entry1->amount = $sum;
            $entry1->save();

            $entry2 = new TransactionEntry();
            $entry2->transaction_id = $transaction->id;
            $entry2->account_id = $payer->id; // ID рахунку клієнта для кредиту
            $entry2->entry_type = 'кредит';
            $entry2->amount = $sum;
            $entry2->save();

            // $invoice->paid += $sum;
            // if($invoice->save()){
            //     $transaction->status = 'проведено';
            //     $transaction->save();
            // }
            Notification::make()
                ->title('Транзакція проведена!')
                ->body('Потрібно здіснити фінансові дії згідно транзакції')
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();
            return $transaction;
         });

    }

    public static function makingPaymentUser(User $user, Account $payer, Account $receiver, float $sum, string $description = null)
    {
         // Використовуємо транзакцію для забезпечення цілісності даних
         return (new static)->getConnection()->transaction(function () use ($user, $payer, $receiver, $sum, $description) {
            //Перевірка обов'язкових полів
            if (!$user|| !$payer || !$receiver || !$sum) {
                throw new \InvalidArgumentException('Відсутні обов’язкові поля для створення транзакції.');
            }

            // Створення транзакції
            $transaction = new self();
            $transaction->reference_number = 'TXN-' . now()->format('YmdHis') . '-' . uniqid();
            $transaction->description = $description ?? 'Оплата за накладну: ' . $invoice->invoice_number;
            $transaction->transaction_date = now();
            $transaction->status = 'проведено';
            $transaction->user_id = auth()->id();
            // $transaction->customer_id = $customer->id;
            $transaction->invoice_id = $user->id;
            $transaction->save();

            // Створення двох записів TransactionEntry
            $entry1 = new TransactionEntry();
            $entry1->transaction_id = $transaction->id;
            $entry1->account_id = $receiver->id; // ID рахунку для дебету
            $entry1->entry_type = 'дебет';
            $entry1->amount = $sum;
            $entry1->save();

            $entry2 = new TransactionEntry();
            $entry2->transaction_id = $transaction->id;
            $entry2->account_id = $payer->id; // ID рахунку клієнта для кредиту
            $entry2->entry_type = 'кредит';
            $entry2->amount = $sum;
            $entry2->save();

            // $invoice->paid += $sum;
            // if($invoice->save()){
            //     $transaction->status = 'проведено';
            //     $transaction->save();
            // }
            Notification::make()
                ->title('Транзакція проведена!')
                ->body('Потрібно здіснити фінансові дії згідно транзакції')
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();
            return $transaction;
         });

    }

}
