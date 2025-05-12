<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Material;
use App\Models\Production;
use App\Models\ProductionMaterial;
use App\Models\ProductionStage;
use App\Models\TemplateProduction;
use App\Models\User;
use App\Models\Warehouse;
use Filament\Notifications\Notification;
use PhpParser\Node\Stmt\Nop;

class ProductionService
{
    /**
     * @param array $data
     * @return Production
     */
    public static function createProduction(array $data): Production
    {
        // Використовуємо транзакцію для забезпечення цілісності даних
        return \DB::transaction(function () use ($data) {

            if(isset($data['template_productions_id']) and !empty($data['template_productions_id']))
            {
                $template = TemplateProduction::findOrFail($data['template_productions_id']);
                if(empty($data['name'])){
                    $data['name'] = $template->name;
                    $data['description'] = $template->description;
                }
            }

           // dd($data );
            // Створення виробництва
            $production = Production::create([
                'name' => $data['name'] ?? $template->name,
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
                'tempalte_id' => $data['template_id'] ?? null,
            ]);


            $warehouse = Warehouse::findOrFail($data['warehouse_id']); // ID складу
            if(isset($template))
            {
                // Додавання матеріалів до виробництва з шаболону
                foreach ($template->materials as $material) {
                    $materialModel = Material::findOrFail($material->material_id);
                    if($material->warehouse_id != null){
                        $warehouseTemplate = Warehouse::findOrFail($material->warehouse_id);
                    }else{
                        $warehouseTemplate = $warehouse;
                    }
                    //dd($warehouseTemplate );
                    ProductionService::setProductionMaterial($production, $materialModel, $warehouseTemplate, $material->quantity*$production->quantity);
                    //$production->addProductionMaterial($materialModel, $warehouse, $material['quantity']); //передаємо модель матерілау, скалду і потрібно дати дані
                }
            }else{
                // Додавання матеріалів до виробництва
                foreach ($data['productionMaterials'] as $material) {
                    $materialModel = Material::findOrFail($material['material_id']);
                    ProductionService::setProductionMaterial($production, $materialModel, $warehouse, $material['quantity']);
                    //$production->addProductionMaterial($materialModel, $warehouse, $material['quantity']); //передаємо модель матерілау, скалду і потрібно дати дані
                }
            }


            if(isset($template))
            {
                 // Додавання етапів виробництва
                foreach ($template->stages as $stage) {
                    ProductionService::setStage($production, $stage->toArray());
                }
            }else{
                 // Додавання етапів виробництва
                foreach ($data['productionStages'] as $stage) {
                    ProductionService::setStage($production, $stage);
                }
            }


            // Додавання розмірів до виробництва
            // if (!empty($data['productionSizes'])) {
            //     $production->addProductionSize($data['productionSizes']);
            // }
            Notification::make()
                ->title('Проведено транзакцію записів!')
                ->warning()
                ->send();
            // Повертаємо створене виробництво
            return $production;
        });
    }

    /**
     * @param Production $production
     * @param array $data
     * @return Production
     */
    public static function updateProduction(Production $production, array $data)
    {
        return \DB::transaction(function () use ($production, $data) {
            if(!empty($production->template_production_id))
            {
                $template = TemplateProduction::findOrFail($production->template_production_id);
            }

            if($data['quantity'] != $production->quantity){
                foreach($production->productionMaterials as $material){
                    $quantityMaterial = $material->quantity/$production->quantity;
                    $material->quantity = $quantityMaterial*$data['quantity'];
                    $material->save();
                    //dd($data['quantity'], $production->quantity, $quantityMaterial, $material->quantity, $quantityMaterial*$data['quantity']);
                }
                Notification::make()
                    ->title('Перераховано кількість матеріалів для виробництва!')
                    ->success()
                    ->send();
            }
            $production->update([
                'name' => $data['name'] ?? $production->name,
                'description' => $data['description'] ?? $production->description,
                'quantity' => $data['quantity'] ?? $production->quantity,
            ]);


            $production->save();
            $production->getTotalCostWithStagesAndMarkup(); // перерахунок вартості
            Notification::make()
                ->title('Змінено дані про виробництво!')
                ->success()
                ->send();
        });
    }

    /**
     * @param Production $production
     * @return bool
     */
    public static function deleteProduction(Production $production): bool
    {
        return Production::getConnection()->transaction(function () use ($production) {
            return $production->delete();
        });
    }

    /**
     * @param Production $production
     * @return bool
     */
    public static function restoreProduction(Production $production): bool
    {
        return Production::getConnection()->transaction(function () use ($production) {
            return $production->restore();
        });
    }

    public static function setProductionMaterial(Production $production, Material $material, Warehouse $warehouse, $quantity)
    {
        $existingMaterial = $production->productionMaterials()->where('material_id', $material->id)->first();
        if ($existingMaterial) {
            // Якщо матеріал вже існує, оновлюємо його кількість
            $existingMaterial->quantity = $quantity;
            $existingMaterial->warehouse_id = $warehouse->id; // ID складу
            $existingMaterial->save();
            Notification::make()
                ->title('Матеріал успішно оновлені у виробництві!')
                ->success()
                ->send();
        }else{
            // Якщо матеріал не існує, створюємо новий запис
            $production->productionMaterials()->create([
                'material_id' => $material->id, // ID матеріалу
                'warehouse_id' => $warehouse->id, // ID складу
                'quantity' => $quantity, // Кількість матеріалу
                'warehouse_id' => $warehouse->id, // ID складу
                'description' => $description ?? null, // Опис матеріалу
            ]);
            Notification::make()
                ->title('Матеріал успішно додано до виробництва!')
                ->success()
                ->send();
        }
    }


    public static function setStage(Production $production, $stage)
    {
        //$stage = $stage['productionStages'];
        // Додаємо етап до виробництва
        return $production->productionStages()->create([
            'name' => $stage['name'],
            'description' => $stage['description'],
            'status' => 'очікує',
            'paid_worker' => $stage['paid_worker'],
            'date' => null,
            'user_id' => $stage['user_id'] ?? auth()->id(),
        ]);
    }


    public static function startStage(ProductionStage $stage)
    {
        $stage->status = 'в роботі';
        Notification::make()
            ->title('Етап виробництва успішно стартував!')
            ->success()
            ->send();
        return $stage->save();
    }


    public static function endStage(ProductionStage $stage)
    {
        $stage->status = 'виготовлено';
        $stage->save();
        $production = $stage->production;
        $production->productionStagesComplited();
        $user = User::find($stage->user->id);
        //$user->paid();
        if($user->paid()){
            Notification::make()
                ->title('Етап виробництва успішно завершено нарахована зарпалата!')
                ->success()
                ->send();
        }
        return $stage->save();
    }

    public static function stopStage(ProductionStage $stage)
    {
        $stage->status = 'скасовано';
        return $stage->save();
    }


    public static function startProduction(Production $production)
    {
        $errors = [];
        //додати перевірку наявності на складі
        foreach($production->productionMaterials as $material){
            if(!$material->checkStockInWarehouse()){
                $errors[] = 'Матеріалу не достатньо на складі: ' . $material->name;
                //throw new \Exception('Матеріалу не достатньо на складі: ' . $material->name);
            }
        }


        ProductionService::setInvoiceOff($production);
        $production->status = 'в роботі';
        $production->save();
        $production->getTotalCostWithStagesAndMarkup(); // перерахунок вартості
        Notification::make()
            ->title('Виробництво стартувало')
            ->success()
            ->send();
        //треба додати створення накладної на списання
    }

    public static function stopProduction(Production $production)
    {
        $production->status = 'скасовано';
        $production->save();
        Notification::make()
            ->title('Виробництво зупинено')
            ->success()
            ->send();
    }

    public static function pauseProduction(Production $production)
    {
        $production->status = 'створено';
        $production->save();
        Notification::make()
            ->title('Виробництво призупинине')
            ->warning()
            ->send();
    }


    public static function addCostomer(Production $production, array $data): Production
    {
        if($data['costomer_id'] == null){
            return $production;
        }
        return Production::getConnection()->transaction(function () use ($production, $data) {
            $production->customers()->attach($data['customer_id'], [
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'description' => $data['description'],
                'date_writing_off' => $data['date_writing_off'],
                'invoice_id' => $data['invoice_id'],
                'warehouse_id' => $data['warehouse_id'],
            ]);
            return $production;
        });
    }

    /**
     * @param array $data
     * @return Invoice
     */
    public static function createInvoice(Production $production, array $data): Invoice
    {
        return \DB::transaction(function () use ($production, $data) {

            $sum = $production->price + $data['addprice'];

           // $sum = $data['addprice'];
            $invoice = Invoice::create([
                'date'  => now(),
                'total' => $sum,
                'paid'  => 0,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'status'    => 'створено',
                'user_id'   => $production->user_id,
                'warehouse_to_id' => $data['warehouse_id'] ?? null,
                'type'  => 'переміщення',
                'notes' => 'Згенерована автоматично наклада переміщення говтої продукції на склад '. $production->name,
            ]);

            $invoice->invoiceProductionItems()->create([
                'production_id'=>$production->id,
                'quantity'=>$production->quantity,
                //'warehouse_productions_id'=>$data['warehouse_id'],
                'price'=>$sum,
                'total'=>$sum*$production->quantity,
            ]);
            Notification::make()
                ->title('Накладна для зарахування виробу створена!')
                ->success()
                ->send();

            return $invoice;
        });
    }

    public static function setInvoiceOff(Production $production)
    {
            return \DB::transaction(function () use ($production) {
                $materialsGrup =  $production->productionMaterials()
                    ->get()
                    ->groupBy('warehouse_id');
                foreach($materialsGrup as $key => $materials){
                    //dd($materials->first(), $key, $materials);
                    $sum = 0.00;
                    foreach($materials as $material){
                        $sum += $material->price * $material->quantity;
                    }
                    $invoice = Invoice::create([
                        'date'  => now(),
                        'total' => $sum,
                        'paid'  => $sum,
                        'warehouse_id' => $key,
                        'status'    => 'створено',
                        'user_id'   => $production->user_id,
                        'type'  => 'списання',
                        'notes' => 'Згенерована автоматично наклада списання матеріалів для виготовлення '. $production->name,
                    ]);

                    //dd( $sum,$invoice);
                    foreach($materials as $material){
                        ProductionService::addMaterialInvoice($material, $invoice);
                    }
                    Notification::make()
                        ->title('Накладна на списання успішно створено!')
                        ->success()
                        ->send();
                }
                //dd($materials);
            });
    }

    public static function addMaterialInvoice(ProductionMaterial $material, Invoice $invoice)
    {
        $invoice->invoiceItems()->create([
            'material_id'   => $material->material_id,
            'quantity'      => $material->quantity,
            'price'         => $material->price,
            'total'         => $material->price * $material->quantity,
        ]);
        $material->invoice_id = $invoice->id;
        $material->save();
    }


    public static function updateMaterialProduction(ProductionMaterial $materialProduction, array $data)
    {

       // exit;
        return \DB::transaction(function () use ($materialProduction, $data) {
            $materialProduction->update([
                'material_id' => $data['material_id'],
                'warehouse_id' => $data['warehouse_id'],
                'quantity' => $data['quantity'],
                //'price' => $data['price'],
                'description' => $data['description'],
            ]);
            Notification::make()
                ->title('Матеріал успішно оновлено у виробництві!')
                ->success()
                ->send();
               // ProductionService::syncFromProduction($materialProduction, $materialProduction->production);
            return $materialProduction;
        });
    }



    public static function syncFromProduction(ProductionMaterial $materialProduction, Production $production)
    {
        return \DB::transaction(function () use ($materialProduction, $production ) {
            $materialsGrup =  $production->productionMaterials()
                ->get()
                ->groupBy('warehouse_id');

            $materialsGrupArray =  $materialsGrup->toArray();
            $materialsGrupKey =  $materialsGrup->keys()->toArray();

                $invoiceOff = $production->invoice_off; // накладні, які вже є
                $matchedInvoices = collect();
                $unmatchedInvoices = collect();

                foreach ($invoiceOff as $invoice) {
                    if (in_array($invoice->warehouse_id, $materialsGrupKey)) {
                        $matchedInvoices->push($invoice);
                    } else {
                        $unmatchedInvoices->push($invoice);
                    }
                }

                dd($matchedInvoices, $unmatchedInvoices);
            Notification::make()
                ->title('Матеріал успішно оновлено у виробництві!')
                ->success()
                ->send();
            return $materialProduction;
        });
    }

    //онвити накладну на списання
    public static function updateMaterialProductionToInvoiceOff(ProductionMaterial $materialProduction, Production $production)
    {
        $materialsGrup =  $production->productionMaterials()
            ->get()
            ->groupBy('warehouse_id')->toArray();


        foreach($production->invoice_off as $invoice){
            // Перевіряємо, чи статус накладної "створено" і чи ID складу матеріалу співпадає з ID складу накладної
            if($invoice->status === 'створено' and isset($materialsGrup[$invoice->warehouse_id])){
                $countGrup += 1;
            }else{

            }
        }

        return \DB::transaction(function () use ($materialProduction, $production) {
            Notification::make()
                ->title('Матеріал успішно оновлено у виробництві!')
                ->success()
                ->send();
            return $materialProduction;
        });
    }

}
