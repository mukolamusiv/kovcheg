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


    public static function makingPayment(Invoice $invoice, Account $payer, Account $receiver, int $sum, string $description = null)
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

       /* $transaction = new self();
        $transaction->reference_number = 'TXN-' . now()->format('YmdHis') . '-' . uniqid();
        $transaction->description = $data['description'].' // Прийняття оплати клієнтом: '.$customer->name.' згідно накладної №'. $invoice->invoice_number ?? null;
        $transaction->transaction_date = now();
        $transaction->status = 'створено';
        $transaction->user_id = auth()->id();
        //$transaction->customer_id = $customer->id;
        $transaction->invoice_id = $invoice->id;
        $transaction->save();

        // Створення двох записів TransactionEntry
        $entry1 = new TransactionEntry();
        $entry1->transaction_id = $transaction->id;
        $entry1->account_id = $data['account_id']; // ID рахунку для дебету
        $entry1->entry_type = 'дебет';
        $entry1->amount = $data['amount'];
        $entry1->save();

        $entry2 = new TransactionEntry();
        $entry2->transaction_id = $transaction->id;
        $entry2->account_id = $payer->id; // ID рахунку клієнта для кредиту
        $entry2->entry_type = 'кредит';
        $entry2->amount = $data['amount'];
        $entry2->save();


        $invoice->paid += $data['amount'];
        if($invoice->save()){
            $transaction->status = 'проведено';
            $transaction->save();
        }

        //dd($transaction, $entry1, $entry2);

*/





/*




        // Перевірка обов'язкових полів
        if (!isset($data['reference_number'], $data['transaction_date'], $data['status'], $data['user_id'], $data['invoice_id'], $data['entries'])) {
            throw new \InvalidArgumentException('Відсутні обов’язкові поля для створення транзакції.');
        }

        // Початок транзакції бази даних
        return \DB::transaction(function () use ($data) {
            // Створення транзакції
            $transaction = new self();
            $transaction->reference_number = $data['reference_number'];
            $transaction->description = $data['description'] ?? null;
            $transaction->transaction_date = $data['transaction_date'];
            $transaction->status = $data['status'];
            $transaction->user_id = $data['user_id'];
            $transaction->customer_id = $data['customer_id'] ?? null;
            $transaction->supplier_id = $data['supplier_id'] ?? null;
            $transaction->invoice_id = $data['invoice_id'];
            $transaction->save();

            // Створення записів транзакції
            foreach ($data['entries'] as $entry) {
                if (!isset($entry['account_id'], $entry['entry_type'], $entry['amount'])) {
                    throw new \InvalidArgumentException('Відсутні обов’язкові поля для створення запису транзакції.');
                }

                // Створення запису транзакції через модель
                $transactionEntry = new \App\Models\TransactionEntry();
                $transactionEntry->transaction_id = $transaction->id;
                $transactionEntry->account_id = $entry['account_id'];
                $transactionEntry->entry_type = $entry['entry_type'];
                $transactionEntry->amount = $entry['amount'];
                $transactionEntry->save();

                // Оновлення балансу рахунку
                $account = \App\Models\Account::findOrFail($entry['account_id']);
                if ($entry['entry_type'] === 'дебет') {
                    $account->balance += $entry['amount'];
                } elseif ($entry['entry_type'] === 'кредит') {
                    $account->balance -= $entry['amount'];
                }
                $account->save();
            }

            return $transaction;
        });
        */
    }

}
