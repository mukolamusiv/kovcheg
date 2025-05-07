<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Notifications\Notification;

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
        'warehouse_id',
        'price',
        'production_date',
        'image',
        'template_production_id'
    ];


    protected static function booted()
    {
        static::creating(function ($production) {
            $production->user_id = auth()->id();
            //$production->price = 0;

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
            $production->price = $production->getTotalCostWithStagesAndMarkup();
        });

        static::updated(function($production){
            if($production->price == 0.00){
                $production->price =  $production->getTotalCostWithStagesAndMarkup();
                $production->save();
            Notification::make()
                ->title('Встановлено вартість продукту!')
                ->warning()
                ->send();
            }
        });

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

    // public function invoiceProductionItems()
    // {
    //     return $this->hasMany(InvoiceProductionItem::class);
    // }

    public function invoiceItems()
    {
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

    public function invoice_on()
    {
        return $this->hasOneThrough(
            Invoice::class,
            InvoiceProductionItem::class,
            'warehouse_productions_id', // Foreign key on InvoiceProductionItem table
            'id',            // Foreign key on Invoice table
            'id',            // Local key on Production table
            'invoice_id'     // Local key on InvoiceProductionItem table
        )->whereIn('type', ['постачання', 'переміщення']);
    }


    public function invoice_off()
    {

       return $this->hasOneThrough(
            Invoice::class,
            ProductionMaterial::class,
            'productions_id', // Foreign key on ProductionMaterial table
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



     /**
     * Створює нове виробництво з переданими даними.
     *
     * @param array $data Дані для створення виробництва.
     * @return Production Створене виробництво.
     */
    public static function createProduction($data)
    {
        // Використовуємо транзакцію для забезпечення цілісності даних
        return (new static)->getConnection()->transaction(function () use ($data) {
            // Створення виробництва
            $production = self::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => 'створено',
                'type' => $data['type'] ?? 'замовлення',
                'pay' => $data['pay'] ?? 'не оплачено',
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'quantity' => $data['quantity'] ?? 1,
                'price' => 0,
                'production_date' =>  null,
                'image' => null,
            ]);

            $warehouse = Warehouse::findOrFail($data['warehouse_id']); // ID складу
            // Додавання матеріалів до виробництва
            foreach ($data['productionMaterials'] as $material) {
                $materialModel = Material::findOrFail($material['material_id']);
                $production->addProductionMaterial($materialModel, $warehouse, $material['quantity']); //передаємо модель матерілау, скалду і потрібно дати дані
            }

            // Додавання етапів виробництва
            foreach ($data['productionStages'] as $stage) {
                $production->addStage($stage);
            }
            // Додавання розмірів до виробництва
            if (!empty($data['productionSizes'])) {
                $production->addProductionSize($data['productionSizes']);
            }
            Notification::make()
                ->title('Проведено транзакцію записів!')
                ->warning()
                ->send();
            // Повертаємо створене виробництво
            return $production;
        });
    }

    /**
     * Додає матеріал до виробництва.
     *
     * @param Material $material Модель матеріалу.
     * @param Warehouse $warehouse Модель складу.
     * @param int $quantity Кількість матеріалу (за замовчуванням 1).
     * @param string|null $description Опис матеріалу (за замовчуванням null).
     * @return ProductionMaterial Доданий матеріал.
     */
    public function addProductionMaterial(Material $material, Warehouse $warehouse, $quantity = 1, $description = null)
    {
        // Перевірка, чи матеріал вже існує у виробництві
        $existingMaterial = $this->productionMaterials()->where('material_id', $material->id)->first();
        if ($existingMaterial) {
            // Якщо матеріал вже існує, оновлюємо його кількість
            $existingMaterial->quantity = $quantity;
            $existingMaterial->warehouse_id = $warehouse->id; // ID складу
            //оновлюємо ціну
            $existingMaterial->price = $material->getTotalValueInWarehouse($warehouse->id);
            $existingMaterial->save();
            return $existingMaterial;
        }else{
            if (!isset($material)) {
                throw new \Exception('Не передано material_id для одного з матеріалів.');
            }
            // Якщо матеріал не існує, створюємо новий запис
            $this->productionMaterials()->create([
                'material_id' => $material->id, // ID матеріалу
                'warehouse_id' => $warehouse->id, // ID складу
                'quantity' => $quantity, // Кількість матеріалу
                'price' => $material->getTotalValueInWarehouse($warehouse->id), // Ціна матеріалу
                'warehouse_id' => $warehouse->id, // ID складу
                'description' => $description ?? null, // Опис матеріалу
            ]);
            Notification::make()
                ->title('Матеріал успішно додано до виробництва!')
                ->success()
                ->send();
            return $this->productionMaterials()->where('material_id', $material['material_id'])->first();
        }
    }

    /**
     * Додає етап до виробництва.
     *
     * @param array $stage Дані етапу.
     * @return ProductionStage Доданий етап.
     */
    public function addStage($stage)
    {
        if($stage['user_id'] == null){
            $stage['user_id'] = auth()->id();
        }
        //$stage = $stage['productionStages'];
        // Додаємо етап до виробництва
        return $this->productionStages()->create([
            'name' => $stage['name'],
            'description' => $stage['description'],
            'status' => $stage['status'],
            'paid_worker' => $stage['paid_worker'],
            'date' => $stage['date'],
            'user_id' => $stage['user_id'],
        ]);
    }

    /**
     * Додає розмір до виробництва.
     *
     * @param object $size Дані розміру.
     * @return ProductionSize Доданий розмір.
     */
    public function addProductionSize($size)
    {
        // Додаємо розмір до виробництва
        return $this->productionSizes()->create([
            'throat' => $size->throat,
            'redistribution' => $size->redistribution,
            'behind' => $size->behind,
            'hips' => $size->hips,
            'length' => $size->length,
            'sleeve' => $size->sleeve,
            'shoulder' => $size->shoulder,
            'comment' => $size->comment,
        ]);
    }

    public function getTotalCost()
    {
        return $this->productionMaterials->sum(function ($material) {
            if($material->price < 1){
                Notification::make()
                    ->title('Деякі товари відсутні на складі!')
                    ->danger()
                    ->send();
            }
            return $material->price * $material->quantity;
        });
    }
    public function getTotalCostWithStages()
    {
        $totalCost = $this->getTotalCost();
        $totalStagesCost = $this->productionStages->sum('paid_worker');
        return $totalCost + $totalStagesCost;
    }
    /**
     * Отримує загальну вартість виробництва з урахуванням етапів та націнки.
     *
     * @return float Загальна вартість виробництва з урахуванням етапів та націнки.
     */
    public function getTotalCostWithStagesAndMarkup()
    {
        $totalCost = $this->getTotalCostWithStages();
        return $totalCost; // Додаємо 40% до загальної вартості
    }


    // НАКЛАДНІ
    public function makeInvoice($invoiceId = null, $data)
    {
        if(!empty($invoiceId)){
            $this->invoice = Invoice::find($invoiceId);
        }else{
            $invoice = $this->invoice;
        }

        if (!$invoice) {
            $invoice = Invoice::create([
                'customer_id' => $this->customer_id,
                'user_id' => $this->user_id,
                'total' => $this->getTotalCostWithStagesAndMarkup(),
                'paid' => 0,
                'due' => $this->getTotalCostWithStagesAndMarkup(),
                'type' => 'продаж',
                'payment_status' => 'не оплачено',
                'status' => 'створено',
                'notes' => 'Накладна створена для виробництва: ' . $this->name,
            ]);

            $invoice->invoiceProductionItems()->create([
                'production_id' => $this->id,
                'quantity' => $this->quantity,
                'price' => $this->price,
                'total' => $this->quantity * $this->price,
            ]);
            Notification::make()
                ->title('Створено накладу для продажу!')
                ->success()
                ->send();
        }


        return $invoice;
    }


    public function makeInvoiceOff()
    {
        $invoice = $this->invoice_off;

        if (!$invoice) {
            $invoice = Invoice::create([
                'customer_id' => $this->customer_id,
                'user_id' => $this->user_id,
                'total' => $this->getTotalCostWithStagesAndMarkup(),
                'paid' =>  $this->getTotalCostWithStagesAndMarkup(),
                'due' => 0,
                'type' => 'списання',
                'payment_status' => 'оплачено',
                'status' => 'створено',
                'notes' => 'Накладна створена для списання матеріалів виробництва: ' . $this->name,
            ]);

            $invoice->invoiceProductionItems()->create([
                'production_id' => $this->id,
                'quantity' => $this->quantity,
                'price' => $this->price,
                'total' => $this->quantity * $this->price,
            ]);

           // protected $fillable = ['invoice_id', 'material_id', 'quantity', 'price', 'total'];

            // Додавання матеріалів до накладної
            foreach ($this->productionMaterials as $material) {
                $material->invoice_id = $invoice->id;
                $material->save();
                $invoice->invoiceItems()->create([
                    'material_id' => $material->material_id,
                    'invoice_id' => $invoice->id,
                    'quantity' => $material->quantity,
                    'price' => $material->price,
                    'total' => $material->quantity * $material->price,
                ]);
            }
        //     // Додавання одного матеріалу до накладної

        //    $invoice->invoiceItems()->create([
        //         'material_id' => $this->productionMaterials->first()->material_id,
        //         'quantity' => $this->productionMaterials->first()->quantity,
        //         'price' => $this->productionMaterials->first()->price,
        //         'total' => $this->productionMaterials->first()->quantity * $this->productionMaterials->first()->price,
        //     ]);
            Notification::make()
                ->title('Створено накладу для списання!')
                ->success()
                ->send();
        }

        return $invoice;
    }
}
