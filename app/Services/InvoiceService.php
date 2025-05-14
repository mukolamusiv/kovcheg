<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Production;
use App\Models\Transaction;
use App\Models\WarehouseMaterial;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\In;

class InvoiceService
{
    public static function moveInvoiceToConducted(Invoice $invoice)
    {

        $move = new MoveInvoiceService($invoice);
        $move->moveInvoice();

        // if($invoice->type == 'продаж'){
        //     InvoiceService::moveSelling($invoice);
        // }

        // if($invoice->type == 'постачання')
        // {
        //     InvoiceService::moveIncome($invoice);
        // }

        if($invoice->type == 'списання')
        {


            //dd($move);

            //InvoiceService::moveWritingOff($invoice);
        }






        // if($invoice->type == 'переміщення')
        // {
            // if(!isset($invoice->supplier))
            // {
            //     Notification::make()
            //         ->title('Помилка проведення!')
            //         ->body('Потрібно додати спочатку постачальника щоб можна було провести накладну')
            //         ->icon('heroicon-o-x-circle')
            //         //->icon('heroicon-o-check-circle')
            //         ->danger()
            //         ->send();
            //         return;
            // }
            // if($invoice->supplier->account == null )
            // {
            //     Notification::make()
            //         ->title('Помилка проведення!')
            //         ->body('Потрібно додати спочатку рахунок постачальника щоб можна було провести накладну')
            //         ->icon('heroicon-o-x-circle')
            //         ->danger()
            //         ->send();
            //         return;
            // }else{
                //  $invoice->supplier->setBalans();

                  //зарахування матеріалів на склад
        //         foreach ($invoice->invoiceItems as $item) {
        //             $material = \App\Models\Material::find($item->material_id);
        //             if ($material) {
        //                 // Отримуємо запис про матеріал на складі за матеріалом та складом
        //                 $materialWarehouse = WarehouseMaterial::where('material_id', $item->material_id)
        //                     ->where('warehouse_id', $invoice->warehouse_id)
        //                     ->first();

        //                 // Якщо запис про матеріал на складі існує
        //                 if ($materialWarehouse) {
        //                     // Збільшуємо кількість матеріалу на складі на кількість з накладної
        //                     $materialWarehouse->quantity += $item->quantity;

        //                     // Перевіярємо вартість продукту на складі якщо є нова яка відрізяняється тоді змінюємо
        //                     if($materialWarehouse->price != $item->price){
        //                         $materialWarehouse->price = $item->price;
        //                         Notification::make()
        //                             ->title('Змінено вартість метеріалу на складі!')
        //                             ->body('Потрібно здіснити дії на складі згідно накладної')
        //                             ->icon('heroicon-o-check-circle')
        //                             ->success()
        //                             ->send();
        //                     }

        //                     Notification::make()
        //                         ->title('Змінено залишки метеріалу на складі!')
        //                         ->body('Потрібно здіснити дії на складі згідно накладної')
        //                         ->icon('heroicon-o-check-circle')
        //                         ->success()
        //                         ->send();
        //                     // Зберігаємо оновлений запис
        //                     $materialWarehouse->save();
        //                 } else {
        //                     // Якщо запису про матеріал на складі немає, створюємо новий запис
        //                     WarehouseMaterial::create([
        //                         'material_id' => $item->material_id, // ID матеріалу
        //                         'warehouse_id' => $invoice->warehouse_id, // ID складу
        //                         'quantity' => $item->quantity, // Кількість матеріалу
        //                         'price' => $item->price, // Ціна матеріалу
        //                     ]);
        //                     Notification::make()
        //                         ->title('Додано метеріал на склад!')
        //                         ->body('Потрібно здіснити дії на складі згідно накладної')
        //                         ->icon('heroicon-o-check-circle')
        //                         ->success()
        //                         ->send();

        //                 }

        //             }

        //         // $invoice->supplier->account->balance = $invoice->supplier->calculateObligations();
        //         // $invoice->supplier->account->save();
        //         }

        //     //}



        // $invoice->update([
        //     'status' => 'проведено',
        // ]);
        // $invoice->save();

        // $invoice->customer();
        //     Notification::make()
        //         ->title('Накладна проведена!')
        //         ->body('Потрібно здіснити дії на складі чи касі згідно накладної')
        //         ->icon('heroicon-o-check-circle')
        //         ->success()
        //         ->send();
        // }
    }



    public static function moveIncome(Invoice  $invoice){
        if(!isset($invoice->supplier))
            {
                Notification::make()
                    ->title('Помилка проведення!')
                    ->body('Потрібно додати спочатку постачальника щоб можна було провести накладну')
                    ->icon('heroicon-o-x-circle')
                    //->icon('heroicon-o-check-circle')
                    ->danger()
                    ->send();
                    return;
            }
            if($invoice->supplier->account == null )
            {
                Notification::make()
                    ->title('Помилка проведення!')
                    ->body('Потрібно додати спочатку рахунок постачальника щоб можна було провести накладну')
                    ->icon('heroicon-o-x-circle')
                    ->danger()
                    ->send();
                    return;
            }else{
                $invoice->supplier->setBalans();
                  //зарахування матеріалів на склад
                foreach ($invoice->invoiceItems as $item) {
                    $material = \App\Models\Material::find($item->material_id);
                    if ($material) {
                        // Отримуємо запис про матеріал на складі за матеріалом та складом
                        $materialWarehouse = WarehouseMaterial::where('material_id', $item->material_id)
                            ->where('warehouse_id', $invoice->warehouse_id)
                            ->first();
                        // Якщо запис про матеріал на складі існує
                        if ($materialWarehouse) {
                            // Збільшуємо кількість матеріалу на складі на кількість з накладної
                            $materialWarehouse->quantity += $item->quantity;
                            // Перевіярємо вартість продукту на складі якщо є нова яка відрізяняється тоді змінюємо
                            if($materialWarehouse->price != $item->price){
                                $materialWarehouse->price = $item->price;
                                Notification::make()
                                    ->title('Змінено вартість метеріалу на складі!')
                                    ->body('Потрібно здіснити дії на складі згідно накладної')
                                    ->icon('heroicon-o-check-circle')
                                    ->success()
                                    ->send();
                            }

                            Notification::make()
                                ->title('Змінено залишки метеріалу на складі!')
                                ->body('Потрібно здіснити дії на складі згідно накладної')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->send();
                            // Зберігаємо оновлений запис
                            $materialWarehouse->save();
                        } else {
                            // Якщо запису про матеріал на складі немає, створюємо новий запис
                            WarehouseMaterial::create([
                                'material_id' => $item->material_id, // ID матеріалу
                                'warehouse_id' => $invoice->warehouse_id, // ID складу
                                'quantity' => $item->quantity, // Кількість матеріалу
                                'price' => $item->price, // Ціна матеріалу
                            ]);
                            Notification::make()
                                ->title('Додано метеріал на склад!')
                                ->body('Потрібно здіснити дії на складі згідно накладної')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->send();
                        }
                    }
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

    public static function moveSelling(Invoice  $invoice){
        if(!isset($invoice->customer))
        {
            Notification::make()
                ->title('Помилка проведення!')
                ->body('Потрібно додати спочатку клієнта щоб можна було провести накладну')
                ->icon('heroicon-o-x-circle')
                //->icon('heroicon-o-check-circle')
                ->danger()
                ->send();
                return;
        }
        if($invoice->customer->account == null )
        {
            Notification::make()
                ->title('Помилка проведення!')
                ->body('Потрібно додати спочатку рахунок клієнта щоб можна було провести накладну')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
                return;
        }else{
            $invoice->customer->setBalans();
                  //списання матеріалів зі складу
                foreach ($invoice->invoiceItems as $item) {
                    $material = \App\Models\Material::find($item->material_id);
                    if ($material) {
                        // Отримуємо запис про матеріал на складі за матеріалом та складом
                        $materialWarehouse = WarehouseMaterial::where('material_id', $item->material_id)
                            ->where('warehouse_id', $invoice->warehouse_id)
                            ->first();
                        // Якщо запис про матеріал на складі існує
                        if ($materialWarehouse) {
                            // Зменшуємо кількість матеріалу на складі на кількість з накладної
                            if($materialWarehouse->quantity >= $item->quantity){
                                $materialWarehouse->quantity -= $item->quantity;
                            }else{
                                Notification::make()
                                    ->title('Помилка списання метеріалу на складі!')
                                    ->body('Недостатня кількість матеріалу на складі')
                                    ->icon('heroicon-o-x-circle')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $materialWarehouse->quantity -= $item->quantity;
                            Notification::make()
                                ->title('Змінено залишки метеріалу на складі!')
                                ->body('Потрібно здіснити дії на складі згідно накладної')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->send();
                            // Зберігаємо оновлений запис
                            $materialWarehouse->save();
                        } else {
                            Notification::make()
                                ->title('Помилка цього товару не було на складі!')
                                ->body('Відсутня кількість матеріалу на складі')
                                ->icon('heroicon-o-x-circle')
                                ->danger()
                                ->send();
                        }
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
            $invoice->customer->calculateObligations();
            $invoice->customer->account->balance = $invoice->customer->calculateObligations();
            $invoice->customer->account->save();
        }
    }

    public static function moveWritingOff(Invoice  $invoice){
       //списання матеріалів зі складу
        foreach ($invoice->invoiceItems as $item) {
            $material = \App\Models\Material::find($item->material_id);
            if ($material) {
                // Отримуємо запис про матеріал на складі за матеріалом та складом
                $materialWarehouse = WarehouseMaterial::where('warehouse_id', $invoice->warehouse_id)
                    ->where('material_id', $item->material_id)
                    ->first();
                // Якщо запис про матеріал на складі існує
                if ($materialWarehouse) {
                    // Зменшуємо кількість матеріалу на складі на кількість з накладної
                    if($materialWarehouse->quantity >= $item->quantity){
                        $materialWarehouse->quantity -= $item->quantity;
                    }else{
                        Notification::make()
                            ->title('Помилка списання метеріалу на складі!')
                            ->body('Недостатня кількість матеріалу на складі')
                            ->icon('heroicon-o-x-circle')
                            ->danger()
                            ->send();
                        return;
                    }

                    $materialWarehouse->quantity -= $item->quantity;
                    Notification::make()
                        ->title('Змінено залишки метеріалу на складі!')
                        ->body('Потрібно здіснити дії на складі згідно накладної')
                        ->icon('heroicon-o-check-circle')
                        ->success()
                        ->send();
                    // Зберігаємо оновлений запис
                    $materialWarehouse->save();
                } else {
                    Notification::make()
                        ->title('Помилка цього товару не було на складі!')
                        ->body('Відсутня кількість матеріалу на складі')
                        ->icon('heroicon-o-x-circle')
                        ->danger()
                        ->send();
                }
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
        $invoice->customer->calculateObligations();
        $invoice->customer->account->balance = $invoice->customer->calculateObligations();
        $invoice->customer->account->save();
    }

    public static function moveMoving(Invoice  $invoice){

    }


    public static function moveInvoiceToCreated(Invoice $invoice)
    {
        $move = new MoveInvoiceService($invoice);
        $move->cancelInvoice();
        //    //зарахування матеріалів на склад
        //    foreach ($invoice->invoiceItems as $item) {
        //     $material = \App\Models\Material::find($item->material_id);
        //     if ($material) {
        //         // Отримуємо запис про матеріал на складі за матеріалом та складом
        //         $materialWarehouse = WarehouseMaterial::where('material_id', $item->material_id)
        //             ->where('warehouse_id', $invoice->warehouse_id)
        //             ->first();

        //         // Якщо запис про матеріал на складі існує
        //         if ($materialWarehouse) {
        //             // Зменшуємо кількість матеріалу на складі на кількість з накладної
        //             $materialWarehouse->quantity -= $item->quantity;

        //             Notification::make()
        //                 ->title('Змінено залишки метеріалу на складі!')
        //                 ->body('Потрібно здіснити дії на складі згідно накладної')
        //                 ->icon('heroicon-o-check-circle')
        //                 ->success()
        //                 ->send();
        //             // Зберігаємо оновлений запис
        //             $materialWarehouse->save();
        //         } else {
        //             Notification::make()
        //                 ->title('Помилка цього товару не було на складі!')
        //                 ->body('Відсутня кількість матеріалу на складі')
        //                 ->icon('heroicon-o-x-circle')
        //                 ->danger()
        //                 ->send();


        //         }

        //     }

        // // $invoice->supplier->account->balance = $invoice->supplier->calculateObligations();
        // // $invoice->supplier->account->save();
        // }

        // $invoice->update([
        //     'status' => 'створено',
        // ]);
        // $invoice->save();
        // Notification::make()
        //         ->title('Накладна скасована!')
        //         ->body('Повернути усе на місце що було на складі чи касі перед накладною')
        //         ->icon('heroicon-o-check-circle')
        //         ->warning()
        //         ->send();
    }


    public static function addInvoiceDiscount(Invoice $invoice, $discount)
    {
        //dd($invoice);

        if($invoice->total - $discount < 0){
            Notification::make()
                ->title('Помилка при проведенні знижки!')
                ->body('Сума знижки перевищує загальну суму накладної')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;

        }
        $invoice->update([
            'discount' => $discount,
            //'total' => $invoice->total - $discount,
        ]);
        $invoice->save();
        Notification::make()
            ->title('Знажка змінена!')
            ->body('Знижка додана до накладної')
            ->icon('heroicon-o-check-circle')
            ->success()
            ->send();
    }

    public static function addInvoiceMarkUp(Invoice $invoice, $discount)
    {
       // dd($invoice);
        $invoice->update([
            //'discount' => $discount,
            'total' => $invoice->total + $discount,
        ]);

        $invoice->save();


        Notification::make()
            ->title('Націнка змінена!')
            ->body('Націнка додана до накладної')
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

    public static function addInvoicePayment($invoice, Account $account, $payment)
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


    public static function makeSuppliner(Invoice $invoice, $suppliner)
    {
        //dd($suppliner);
        $invoice->update([
            'supplier_id' => $suppliner,
        ]);
        $invoice->save();
        Notification::make()
            ->title('Постачальник змінений!')
            ->body('Постачальник змінений у накладній')
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
            'discount' => $data['discount'] ?? 0,
            'shipping' => $data['shipping'] ?? 0,
            'status' => 'створено',
            'payment_status' => 'не оплачено',
            'warehouse_id' => $data['warehouse_id'],
        ]);
        $money = 0.00;
        if(isset($data['supply_items'])){
            $data['items'] = $data['supply_items'];
        }
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

            $add = InvoiceService::addMaterialToInvoice($invoice, $item['material_id'], $item['quantity'], $item['price'], true, $data['warehouse_id']);

            // if(!isset($item['price'])){
            //     $item['price'] = $material->getPriceMaterial($data['warehouse_id']);
            // }

            // $invoice->invoiceItems()->create([
            //     'material_id' => $item['material_id'],
            //     'quantity' => $item['quantity'],
            //     'price' => $item['price'],
            //     'total' => $item['price'] * $item['quantity'],
            // ]);
            $money += $add;
        }

        // $data['discount'] - у відсотках
        // $data['shipping'] - у гривнях
        // $money = $money - ($money * $data['discount'] / 100);

        if(!empty($data['saleUp'])){
            $money = $money + ($money * $data['saleUp'] / 100);
        }
        $invoice->total = $money + $data['shipping'];
        $invoice->paid = 0;
        $invoice->due = $money - ($money * $data['discount'] / 100) ?? 0;
        $invoice->warehouse_id = $data['warehouse_id']; // ID складу
        $invoice->save();
        //dd($money,$invoice);
        return $invoice;
        });
    }


    public static function addMaterialToInvoice(Invoice $invoice, $material_id, $quantity, $price = null, $added = false, $warehouse_id )
    {
        //dd($invoice);
        //dd($material_id, $quantity, $price, $added);

        // Перевірка, чи накладна існує
        if (!$invoice) {
            Notification::make()
                ->title('Помилка при додаванні позиції!')
                ->body('Накладна не знайдена')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;
        }

        // Перевірка, чи матеріал існує
        $material = \App\Models\Material::find($material_id);
        if (!$material) {
            Notification::make()
                ->title('Помилка при додаванні позиції!')
                ->body('Матеріал не знайдено у базі даних')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;
        }

        // Перевірка, чи матеріал вже доданий до накладної
        $existingItem = $invoice->invoiceItems()->where('material_id', $material_id)->first();
        if ($existingItem) {
            Notification::make()
                ->title('Помилка при додаванні позиції!')
                ->body('Цей матеріал вже доданий до накладної')
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->send();
            return;
        }



        // if($invoice->type == 'продаж' or $invoice->type == 'постачання'){
        //     $price = $price;
        // }else{
        //     $price = $material->getPriceMaterial($invoice->warehouse_id);
        //             // Перевірка наявності матеріалу на складі
        //     if ($material->quantity < $quantity) {
        //         Notification::make()
        //             ->title('Помилка при додаванні позиції!')
        //             ->body('Недостатня кількість матеріалу на складі')
        //             ->icon('heroicon-o-x-circle')
        //             ->danger()
        //             ->send();
        //         return;
        //     }
        // }

        //dd($material->getMaterialWarehouse($warehouse_id), $invoice->warehouse_id, $warehouse_id);
        if($material->checkMaterialInWarehouse($warehouse_id)){
            if($invoice->type == 'продаж'){
                Notification::make()
                    ->title('Помилка при додаванні позиції!')
                    ->body('Матеріал не знайдено на складі')
                    ->icon('heroicon-o-x-circle')
                    ->danger()
                    ->send();
                        \DB::rollBack();
                    return;
            }

            if($price == null or $price <= 0.01){
                if($material->checkMaterialInWarehouse($warehouse_id)){
                    $price = $material->getMaterialWarehouse($warehouse_id)->first()->price;
                }else{

                }
                // if($price <= $material->getMaterialWarehouse($warehouse_id)->first()->price or $price <= 0.01){
                //     $price = $material->getMaterialWarehouse($warehouse_id)->first()->price;
                // }else{
                //     $price = $material->getMaterialWarehouse($warehouse_id)->first()->price;
                // }
            }else{
                $price = $price;
            }
        }else{
            if($price == null){
                if($material->checkMaterialInWarehouse($warehouse_id)){
                    $price = $material->getMaterialWarehouse($warehouse_id)->first()->price;
                }else{
                    $price = $material->getMaterialWarehouse($warehouse_id)->first()->price;
                }
            }else{
                $price = $price;
            }
        }


        //dd($sale_up);
        $invoice->invoiceItems()->create([
            'material_id' => $material_id,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $price * $quantity,
        ]);
        $invoice->save();

        if($added == true){
            // $invoice->total += $price * $quantity;
            // $invoice->due += $price * $quantity;
            // $invoice->save();
            return $price * $quantity;
        }else{
            $invoice->calculate();
            Notification::make()
                ->title('Матеріал доданий!')
                ->body('Матеріал доданий до накладній')
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();

        }

       // return $invoice;
    }
}

