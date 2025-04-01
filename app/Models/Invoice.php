<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Клас Invoice представляє модель накладної
class Invoice extends Model
{
    use HasFactory, SoftDeletes; // Використання трейтів для фабрик і м'якого видалення

    // Масив $fillable визначає, які поля можна масово заповнювати
    protected $fillable = [
        'invoice_number', // Номер накладної
        'customer_id', // Ідентифікатор клієнта
        'user_id', // Ідентифікатор користувача
        'supplier_id', // Ідентифікатор постачальника
        'invoice_date', // Дата накладної
        'due_date', // Дата оплати
        'total', // Загальна сума
        'paid', // Сума, яка вже оплачена
        'due', // Сума, яка залишилася до оплати
        'discount', // Знижка
        'shipping', // Вартість доставки
        'type', // Тип накладної
        'payment_status', // Статус оплати
        'status', // Загальний статус накладної
        'notes', // Примітки
    ];

    // Відношення до моделі Customer (клієнт)
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Відношення до моделі User (користувач)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Відношення до моделі Supplier (постачальник)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Відношення до моделі InvoiceItem (елементи накладної)
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // Відношення до моделі InvoiceProductionItem (виробничі елементи накладної)
    public function invoiceProductionItems()
    {
        return $this->hasMany(InvoiceProductionItem::class);
    }

    // Відношення до моделі Transaction (транзакції)
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Відношення до моделі ProductionMaterial (матеріали для виробництва)
    public function productionMaterials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    // Метод для узгодження фінансів накладної
    public function reconcileFinances()
    {
        // Розрахунок загальної суми оплати з транзакцій
        $totalPaidFromTransactions = $this->transactions()->with('entries')->get()->reduce(function ($carry, $transaction) {
            return $carry + $transaction->entries->where('entry_type', 'кредит')->sum('amount');
        }, 0);

        // Встановлення статусу оплати залежно від суми, що залишилася
        if ($this->due == 0) {
            $this->payment_status = 'оплачено';
        } elseif ($this->due > 0 && $this->paid > 0) {
            $this->payment_status = 'частково оплачено';
        } else {
            $this->payment_status = 'не оплачено';
        }
        $this->save();

        // Оновлення полів "paid" і "due", якщо є розбіжності
        if ($this->paid != $totalPaidFromTransactions) {
            $this->paid = $totalPaidFromTransactions;
            $this->due = $this->total - $this->paid;
            $this->save();
        }
    }

    // Метод booted для обробки подій моделі
    protected static function booted()
    {
        // Подія перед створенням накладної
        static::creating(function ($invoice) {
            // Визначення типу накладної
            $type = 'F';
            if ($invoice->type == 'продаж') {
                $type = 'C';
            } else if($invoice->type == 'постачання') {
                $type = 'S';
            } else if($invoice->type == 'переміщення') {
                $type = 'T';
            } else if($invoice->type == 'повернення') {
                $type = 'R';
            } else if($invoice->type == 'списання') {
                $type = 'D';
            }

            // Формування номера накладної
            $invoice_number = 'INV-'.$type.'-' . date('Ymd') . '-' . rand(1000, 9999);
            $invoice->invoice_number = $invoice_number;
            $invoice->user_id = auth()->user()->id;

            // Встановлення дати створення накладної
            $invoice->invoice_date = date('Y-m-d');
        });

        // Подія перед оновленням накладної
        static::updating(function($invoice){
            // Розрахунок загальної суми оплати з транзакцій
            $totalPaidFromTransactions = $invoice->transactions()->with('entries')->get()->reduce(function ($carry, $transaction) {
                return $carry + $transaction->entries->where('entry_type', 'кредит')->sum('amount');
            }, 0);

            $invoice->paid = $totalPaidFromTransactions;

            // Розрахунок суми, що залишилася до оплати
            $invoice->due = $invoice->total - $invoice->paid;

            // Встановлення статусу оплати
            if ($invoice->due == 0) {
                $invoice->payment_status = 'оплачено';
            } elseif ($invoice->due > 0 && $invoice->paid > 0) {
                $invoice->payment_status = 'частково оплачено';
            } else {
                $invoice->payment_status = 'не оплачено';
            }
        });
    }

    // Метод для створення накладних для виробництва
    public static function createProductionInvoices(\App\Models\Production $production, $customer_id)
    {
        // Накладна на продаж виробу
        $saleInvoice = new self();
        $saleInvoice->fill([
            'customer_id' => $customer_id ?? null,
            'type' => 'продаж',
            'total' => $production->quantity * $production->price,
            'paid' => 0,
            'due' => $production->quantity * $production->price,
            'payment_status' => 'не оплачено',
            'status' => 'створено',
            'notes' => 'Накладна для продажу готового продукту: ' . $production->name,
        ]);
        $saleInvoice->save();

        // Додавання виробничих елементів до накладної
        $saleInvoice->invoiceProductionItems()->create([
            'production_id' => $production->id,
            'quantity' => $production->quantity,
            'price' => $production->price,
            'total' => $production->quantity * $production->price,
        ]);

        $saleInvoice->save();

        // Накладна на переміщення матеріалів
        // $materials = $production->productionMaterials;

        // if ($materials->isEmpty()) {
        //     throw new \Exception('Матеріали для виробництва не знайдено.');
        // }

        // $transferInvoice = new self();
        // $transferInvoice->fill([
        //     'type' => 'списання',
        //     'total' => $materials->sum(fn($material) => $material->quantity * $material->price),
        //     'paid' => $materials->sum(fn($material) => $material->quantity * $material->price),
        //     'due' => 0,
        //     'payment_status' => 'оплачено',
        //     'status' => 'створено',
        //     'notes' => 'Накладна списання матеріалів для виробництва: ' . $production->name,
        // ]);
        // $transferInvoice->save();

        // // Додавання матеріалів до накладної
        // foreach ($materials as $material) {
        //     $transferInvoice->invoiceItems()->create([
        //         'material_id' => $material->material_id,
        //         'quantity' => $material->quantity,
        //         'price' => $material->price,
        //         'total' => $material->quantity * $material->price,
        //     ]);
        // }

        // // Додавання виробничих елементів до накладної
        // $transferInvoice->invoiceProductionItems()->create([
        //     'production_id' => $production->id,
        //     'quantity' => 0,
        //     'price' => 0,
        //     'total' => 0,
        // ]);

        return [
            'sale_invoice' => $saleInvoice,
            //'transfer_invoice' => $transferInvoice,
        ];
    }


    // Метод для створення накладної на списання матеріалів для виробництва
    public static function createMaterialWriteOffInvoice(\App\Models\Production $production, $totalCost)
    {
        // $materials = $production->productionMaterials;
        // if ($materials->isEmpty()) {
        //     throw new \Exception('Матеріали для виробництва не знайдено.');
        // }

        $writeOffInvoice = new self();
        $writeOffInvoice->fill([
            'type' => 'списання',
            'total' => $totalCost * $production->quantity,
            'paid' => $totalCost * $production->quantity,
            'due' => 0,
            'payment_status' => 'оплачено',
            'status' => 'створено',
            'notes' => 'Накладна списання матеріалів для виробництва: ' . $production->name,
        ]);
        $writeOffInvoice->save();


        $writeOffInvoice->invoiceProductionItems()->create([
            'production_id' => $production->id,
            'quantity' => $production->quantity,
            'price' => $production->price,
            'total' => $production->price * $production->quantity,
        ]);

        // // Додавання матеріалів до накладної
        // foreach ($materials as $material) {
        //     $writeOffInvoice->invoiceItems()->create([
        //         'material_id' => $material->material_id,
        //         'quantity' => $material->quantity,
        //         'price' => $material->price,
        //         'total' => $material->quantity * $material->price,
        //     ]);
        // }

        return $writeOffInvoice;
    }
}
