<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Material;
use App\Models\Production;
use App\Models\TemplateProduction;
use App\Models\Warehouse;
use Filament\Notifications\Notification;

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

            if(isset($data['template_production_id']) and !empty($data['template_production_id']))
            {
                $template = TemplateProduction::findOrFail($data['template_production_id']);
                if(empty($data['name'])){
                    $data['name'] = $template->name;
                    $data['description'] = $template->description;
                }
            }

            // Створення виробництва
            $production = Production::create([
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
                'tempalte_id' => $data['template_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
            ]);

            $warehouse = Warehouse::findOrFail($data['warehouse_id']); // ID складу
            // Додавання матеріалів до виробництва
            foreach ($data['productionMaterials'] as $material) {
                $materialModel = Material::findOrFail($material['material_id']);
                ProductionService::setProductionMaterial($production, $materialModel, $warehouse, $material['quantity']);
                //$production->addProductionMaterial($materialModel, $warehouse, $material['quantity']); //передаємо модель матерілау, скалду і потрібно дати дані
            }

            // Додавання етапів виробництва
            foreach ($data['productionStages'] as $stage) {
                ProductionService::setStage($production, $stage);
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


    public static function setProductionMaterial(Production $production, Material $material, Warehouse $warehouse, $quantity)
    {
        $existingMaterial = $production->productionMaterials()->where('material_id', $material->id)->first();
        if ($existingMaterial) {
            // Якщо матеріал вже існує, оновлюємо його кількість
            $existingMaterial->quantity = $quantity;
            $existingMaterial->warehouse_id = $warehouse->id; // ID складу
            //оновлюємо ціну
            $existingMaterial->price = $material->getTotalValueInWarehouse($warehouse->id);
            $existingMaterial->save();
            return $existingMaterial;
        }else{
            // Якщо матеріал не існує, створюємо новий запис
            $production->productionMaterials()->create([
                'material_id' => $material->id, // ID матеріалу
                'warehouse_id' => $warehouse->id, // ID складу
                'quantity' => $quantity, // Кількість матеріалу
                'price' => $material->getPriceMaterial($warehouse->id)->price, // Ціна матеріалу
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


    /**
     * @param Production $production
     * @param array $data
     * @return Production
     */
    public static function updateProduction(Production $production, array $data): Production
    {
        return Production::getConnection()->transaction(function () use ($production, $data) {
            $production->update([
                'name' => $data['name'],
            ]);

            return $production;
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
        return Invoice::getConnection()->transaction(function () use ($data) {
            $invoice = Invoice::create([
                'number' => $data['number'],
                'date' => $data['date'],
                'customer_id' => $data['customer_id'],
                'total_amount' => $data['total_amount'],
                'status' => $data['status'],
            ]);
            return $invoice;
        });
    }

}
