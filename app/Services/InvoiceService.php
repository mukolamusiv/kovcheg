<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Production;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\In;

class InvoiceService
{
    public static function moveInvoiceToConducted(Invoice $invoice)
    {
        if(!isset($invoice->customer)){
            Notification::make()
                ->title('Помилка проведення!')
                ->body('Потрібно додати спочатку клієнта/постачальника щоб можна було провести накладну')
                ->icon('heroicon-o-x-circle')
                //->icon('heroicon-o-check-circle')
                ->danger()
                ->send();
                return;
        }else{
            if($invoice->customer->account == null){
                Notification::make()
                    ->title('Помилка проведення!')
                    ->body('Потрібно додати спочатку рахунок клієнта/постачальника щоб можна було провести накладну')
                    ->icon('heroicon-o-x-circle')
                    ->danger()
                    ->send();
                    return;
            }else{

                $invoice->customer->calculateObligations();
                $invoice->customer->account->balance = $invoice->customer->calculateObligations();
                $invoice->customer->account->save();
            }
        }
        $invoice->update([
            'status' => 'проведено',
        ]);
        $invoice->save();

        $invoice->customer();
        Notification::make()
                ->title('Накладна проведена!')
                ->body('Потрібно здіснити дії на складі чи касі згідно накладної')
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();
    }

    public static function moveInvoiceToCreated(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'створено',
        ]);
        $invoice->save();
        Notification::make()
                ->title('Накладна скасована!')
                ->body('Повернути усе на місце що було на складі чи касі перед накладною')
                ->icon('heroicon-o-check-circle')
                ->warning()
                ->send();
    }


    public static function addInvoiceDiscount(Invoice $invoice, $discount)
    {
        dd($invoice);
        $invoice->update([
            'discount' => $discount,
        ]);
        $invoice->save();
        Notification::make()
            ->title('Знажка змінена!')
            ->body('Знижка додана до накладної')
            ->icon('heroicon-o-check-circle')
            ->success()
            ->send();
    }


    public static function addInvoiceDelivery($invoice, $delivery)
    {
        $invoice->update([
            'shipping' => $delivery,
        ]);
        $invoice->save();
        Notification::make()
            ->title('Доставка змінена!')
            ->body('Доставка додана до накладної')
            ->icon('heroicon-o-check-circle')
            ->success()
            ->send();
    }

    public static function addInvoicePayment($invoice, Account $account, int $payment)
    {
       // dd($payment, $account, $invoice);
        // Перевірка, чи сума платежу не перевищує залишок
        if ($payment > $invoice->due) {
            Notification::make()
                ->title('Помилка при проведенні оплати!')
                ->body('Сума платежу перевищує залишок по накладній')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;
        }
        // Перевірка, чи сума платежу не менше нуля
        if ($payment <= 0) {
            Notification::make()
                ->title('Помилка при проведенні оплати!')
                ->body('Сума платежу повинна бути більше нуля')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;
        }
        // Перевірка, чи рахунок існує
        if (!$account) {
            Notification::make()
                ->title('Помилка при проведенні оплати!')
                ->body('Рахунок не знайдено')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;
        }
        if($invoice->type == 'продаж'){
            $payer = $invoice->customer->account; // ID рахунку клієнта
            $receiver = $account; // ID рахунку компанії
            $description = 'Оплата клієнтом згідно накладної №'.$invoice->invoice_number; // Опис транзакції
        }
        if($invoice->type == 'постачання'){
            $payer = $account; // ID рахунку платника
            $receiver = $invoice->supplier->account; // ID рахунку постачальника
            $description = 'Оплата постачальнику згідно накладної №'.$invoice->invoice_number; // Опис транзакції
        }
        //dd($payer, $receiver, $description);
        $transaction = Transaction::makingPayment(
            $invoice,
            $payer,
            $receiver,
            $payment,
            $description
        );

        if ($transaction) {
            $invoice->paid += $payment;
            $invoice->due -= $payment;
            $invoice->save();
            Notification::make()
                ->title('Оплата проведена!')
                ->body('Потрібно здіснити фінансові дії згідно накладної')
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Помилка при проведенні оплати!')
                ->body('Не вдалося провести оплату за накладною')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
        }

    }

    public static function makeCustumer(Invoice $invoice, $customer)
    {
        $invoice->update([
            'customer_id' => $customer,
        ]);
        $invoice->save();
        Notification::make()
            ->title('Клієнт змінений!')
            ->body('Клієнт змінений у накладній')
            ->icon('heroicon-o-check-circle')
            ->success()
            ->send();
    }



    public static function addProduction(Invoice $invoice, Production $production)
    {

    }


    public static function makeInvoice($data)
    {
        return \DB::transaction(function () use ($data) {
        $invoice = Invoice::create([
            'customer_id' => $data['customer_id'],
            'supplier_id' => $data['supplier_id'],
            'type' => $data['type'],
            'total' => 0,
            'paid' => 0,
            'due' => 0,
            'status' => 'створено',
            'payment_status' => 'не оплачено',
        ]);
        $money = 0;
        // Додати позиції до накладної
        foreach ($data['items'] as $item) {
            // Перевірка наявності матеріалу на складі
            $material = \App\Models\Material::find($item['material_id']);
            if (!$material) {
                Notification::make()
                    ->title('Помилка при додаванні позиції!')
                    ->body('Матеріал не знайдено')
                    ->icon('heroicon-o-x-circle')
                    ->danger()
                    ->send();
                continue;
            }


            if(!isset($item['price'])){
                $item['price'] = $material->getPriceMaterial($data['warehouse_id']);
            }

            $invoice->invoiceItems()->create([
                'material_id' => $item['material_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);
            $money += $item['price'] * $item['quantity'];
        }

        $invoice->total = $money;
        $invoice->paid = 0;
        $invoice->due = $money;
        $invoice->save();
        //dd($money,$invoice);
        return $invoice;
        });
    }
}

