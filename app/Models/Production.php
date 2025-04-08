<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use function PHPUnit\Framework\isNull;

class Production extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'type',
        'pay',
        'customer_id',
        'user_id',
        'quantity',
        'price',
        'production_date',
        'image'
    ];


    protected static function booted()
    {
        static::creating(function ($production) {
            $production->user_id = auth()->id();
            $production->price = 0;

            // if(!empty($production->customer_id)){
            //     $invoice = new Invoice();
            //     $invoice->fill([
            //         //'invoice_number' => 'INV-' . now()->timestamp,
            //         'customer_id' => $production->customer_id,
            //         //'user_id' => auth()->id(),
            //         //'invoice_date' => now(),
            //         //'due_date' => now()->addDays(30),
            //         'total' => 0,
            //         // $table->decimal('total', 15, 2); // Сума
            //         // $table->decimal('paid', 15, 2); // Оплачено
            //         'paid' => 0,
            //         //'due' => $production->quantity * $production->price,
            //         'type' => 'продаж',
            //         'payment_status' => 'не оплачено',
            //         'status' => 'створено',
            //         'notes' => 'Накладна створена для виробництва: ' . $production->name. ' і клієнта: '. $production->customer->name,
            //     ]);
            //     $invoice->save();
            //     $invoice->invoiceProductionItems()->create([
            //         'production_id' => $production->id,
            //         'quantity' => $production->quantity,
            //         'price' => $production->price,
            //         'total' => $production->quantity * $production->price,
            //     ]);

            //   //  dd($invoice);

            //     // $customer->account()->create([
            //     //     'name' => 'Рахунок клієнта - ' . $customer->name,
            //     //     'description' => 'Фінансовий рахунок клієнта',
            //     //     'account_type' => 'актив',
            //     //     'account_category' => 'клієнт',
            //     //     'currency' => 'UAH',
            //     //     'balance' => 0.00,
            //     // ]);
            // } else{
            //    // dd('не спрацювало', $production);
            // }
        });

        static::created(function ($production) {
            //Invoice::createProductionInvoices($production);
        });

        // static::update(function(){
        //     $this->custumer_id;
        //     // Якщо ідентифікатор клієнта не є null

        // });

    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function productionMaterials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    public function productionMaterialsTotalCost()
    {
        return $this->productionMaterials->sum(function ($material) {
            return $material->price * $material->quantity;
        });
    }

    public function productionStages()
    {
        return $this->hasMany(ProductionStage::class);
    }

    public function invoiceProductionItems()
    {
        return $this->hasMany(InvoiceProductionItem::class);
    }

    public function invoiceItems()
    {
        // dd ($this->hasManyThrough(
        //     InvoiceItem::class,
        //     InvoiceProductionItem::class,
        //     'production_id', // Зовнішній ключ у таблиці InvoiceProductionItem
        //     'invoice_id',    // Зовнішній ключ у таблиці InvoiceItem
        //     'id',            // Локальний ключ у таблиці Production
        //     'invoice_id'     // Локальний ключ у таблиці InvoiceProductionItem
        // ));
        return $this->hasManyThrough(
            InvoiceItem::class,
            InvoiceProductionItem::class,
            'production_id', // Foreign key on InvoiceProductionItem table
            'invoice_id',    // Foreign key on InvoiceItem table
            'id',            // Local key on Production table
            'invoice_id'     // Local key on InvoiceProductionItem table
        )->whereHas('invoice', function ($query) {
            $query->where('type', 'переміщення');
        });
    }

    public function invoice()
    {
        return $this->hasOneThrough(
            Invoice::class,
            InvoiceProductionItem::class,
            'production_id', // Foreign key on InvoiceProductionItem table
            'id',            // Foreign key on Invoice table
            'id',            // Local key on Production table
            'invoice_id'     // Local key on InvoiceProductionItem table
        )->whereIn('type', ['продаж']);
    }

    // public function invoice_off()
    // {
    //     return $this->hasOneThrough(
    //         Invoice::class,
    //         InvoiceProductionItem::class,
    //         'production_id', // Foreign key on InvoiceProductionItem table
    //         'id',            // Foreign key on Invoice table
    //         'id',            // Local key on Production table
    //         'invoice_id'     // Local key on InvoiceProductionItem table
    //     )->whereIn('type', ['переміщення', 'списання']);
   // }

    public function invoice_off()
    {
        return $this->hasOneThrough(
            Invoice::class,
            ProductionMaterial::class,
            'production_id', // Foreign key on ProductionMaterial table
            'id',            // Foreign key on Invoice table
            'id',            // Local key on Production table
            'invoice_id'     // Local key on ProductionMaterial table
        )->whereIn('type', ['списання', 'переміщення']);
    }


    public function productionSizes()
    {
        return $this->hasOne(ProductionSize::class);
    }






    public function customer_pay($data){
        $invoice = $this->invoice;

        if (!$invoice) {
            throw new \Exception('Накладна не знайдена для цього виробництва.');
        }

        $account = $this->customer->account;

        if (!$account) {
            throw new \Exception('Акаунт клієнта не знайдено.');
        }

        $amount = $data['amount'];

        if ($amount <= 0) {
            throw new \Exception('Сума оплати повинна бути більше нуля.');
        }

        if ($amount > $invoice->due) {
            throw new \Exception('Сума оплати перевищує заборгованість.');
        }

        // Оновлення балансу акаунта клієнта
        $account->balance += $amount;
        $account->save();

        // Створення транзакції
        $transaction = $invoice->transactions()->create([
            'reference_number' => 'TRX-' . now()->timestamp,
            'description' => 'Оплата за накладну: ' . $invoice->invoice_number,
            'transaction_date' => now(),
            'status' => 'створено',
            'user_id' => auth()->id(),
        ]);

        // Додавання записів транзакції
        $transaction->transactionEntries()->create([
            'account_id' => $account->id,
            'entry_type' => 'дебет',
            'amount' => $amount,
        ]);

        $transaction->transactionEntries()->create([
            'account_id' => $invoice->customer->account->id,
            'entry_type' => 'кредит',
            'amount' => $amount,
        ]);

        // Оновлення даних накладної
        $invoice->paid += $amount;
        $invoice->due -= $amount;

        if ($invoice->due == 0) {
            $invoice->payment_status = 'оплачено';
        } elseif ($invoice->paid > 0) {
            $invoice->payment_status = 'частково оплачено';
        }

        $invoice->save();
    }




    public static function createProductionWithDetails(array $data)
    {
        // Використовуємо транзакцію для забезпечення цілісності даних
        return (new static)->getConnection()->transaction(function () use ($data) {

             // Розрахунок загальної вартості виробництва
             $totalCost = 0;
             $materialArray = array();
             // Додаємо вартість матеріалів
             if (!empty($data['productionMaterials'])) {
                 foreach ($data['productionMaterials'] as $material) {
                    $materialData = Material::find($material['material_id']);
                    //dd($materialData->getTotalValueInWarehouse($data['warehouse_id']));
                    if ($materialData) {
                        $price = $materialData->getTotalValueInWarehouse($data['warehouse_id']);
                        $materialArray[] = [
                            'material_id' => $materialData->id,
                            'name' => $materialData->name,
                            'quantity' => $material['quantity'] ?? 1,
                            'price' => $price,//$materialData->getTotalValueInWarehouse($data['warehouseId']),//$data['warehouseId']),
                            'description' => $material['description'],
                            'date_writing_off' => $material['date_writing_off'] ?? null,
                           // 'total_cost' => ($material['quantity'] ?? 1) * ($material['price'] ?? $materialData->price),
                        ];
                    }
                     $totalCost += ($material['quantity'] ?? 1) * ($price ?? 0);
                 }
             }

             // Додаємо вартість етапів виробництва (оплата працівникам)
             if (!empty($data['productionStages'])) {
                 foreach ($data['productionStages'] as $stage) {
                     $totalCost += $stage['paid_worker'] ?? 0;
                 }
             }

             $Cost = $totalCost;
             $totalCost = $totalCost*1.4;

            // $production->price

            // Створення виробництва
            $production = self::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'створено',
                'type' => $data['type'] ?? 'замовлення',
                'pay' => $data['pay'] ?? 'не оплачено',
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'quantity' => $data['quantity'] ?? 1,
                'price' => $totalCost,
                'production_date' => $data['production_date'] ?? null,
                'image' => $data['image'] ?? null,
            ]);



            $production->price;
            // dd($production,$totalCost);
            $production->save();
            // Додавання етапів виробництва
            if (!empty($data['productionStages'])) {
                foreach ($data['productionStages'] as $stage) {
                    $production->productionStages()->create([
                        'name' => $stage['name'],
                        'description' => $stage['description'] ?? null,
                        'status' => $stage['status'] ?? 'очікує',
                        'paid_worker' => $stage['paid_worker'] ?? 0,
                        'date' => $stage['date'] ?? null,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            $production->price;
            $production->save();
            $invoice_off = Invoice::createMaterialWriteOffInvoice($production, $Cost);

            // Додавання матеріалів до виробництва
            if (!empty($materialArray)) {
                foreach ($materialArray as $material) {
                    $production->productionMaterials()->create([
                        'material_id' => $material['material_id'],
                        'invoice_id' => $invoice_off->id,
                        'quantity' => $material['quantity'] ?? 1,
                        'price' => $material['price'] ?? 0,
                        'description' => $material['description'] ?? null,
                        'date_writing_off' => $material['date_writing_off'] ?? null,
                    ]);
                }
            }

            // if(!empty($data['customer_id']))
            // {
            //     $customer = Customer::find($data['customer_id']);
            // }

            // // Додавання розмірів до виробництва
            // if (!empty($customer->size)) {
            //     $production->productionSizes()->create([
            //         'throat' => $customer->size->throat ?? null,
            //         'redistribution' => $customer->size->redistribution ?? null,
            //         'behind' => $customer->size->behind ?? null,
            //         'hips' => $customer->size->hips ?? null,
            //         'length' => $customer->size->length ?? null,
            //         'sleeve' => $customer->size->sleeve ?? null,
            //         'shoulder' => $customer->size->shoulder ?? null,
            //         'comment' => $customer->size->comment ?? null,
            //     ]);
            // }



            // // Оновлюємо вартість виробництва
            $production->price = $totalCost;
            $production->save();
            //dd($production->load(['productionStages', 'productionMaterials', 'productionSizes']));
            // Повертаємо створене виробництво разом із пов'язаними даними
            return $production->load(['productionStages', 'productionMaterials', 'productionSizes']);
        });
    }
}
