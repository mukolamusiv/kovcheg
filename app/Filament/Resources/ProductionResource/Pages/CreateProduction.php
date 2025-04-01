<?php

namespace App\Filament\Resources\ProductionResource\Pages;

use App\Filament\Resources\ProductionResource;
use App\Models\Invoice;
use App\Models\Production;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProduction extends CreateRecord
{
    protected static string $resource = ProductionResource::class;

    public function create(bool $another = false): void
    {
        // Перевірка валідності даних та розрахунок ціни продукту
        $this->validate();

        $production = Production::createProductionWithDetails($this->data);

        Invoice::createProductionInvoices($production, $this->data['customer_id']);
         // Сповіщення про успішне створення
            Notification::make()
            ->title('Створено нове виробництво додані накладні та складено список метеріалів!')
            ->success()
            ->send();

        // Перенаправлення на сторінку перегляду або список записів
       // $this->redirect(ProductionResource::getUrl('index'));
        $this->redirect(ProductionResource::getUrl('view', ['record' => $production->id]));
        // Зупинка подальшого виконання
        return;
       // dd($production);
       // $invoice = Invoice::createProductionInvoices($production);

        //return $production;
        // // Отримання матеріалів та розрахунок загальної вартості сировини
        // $productionMaterials = $this->data['production_materials'] ?? [];
        // $rawMaterialCost = 0;

        // foreach ($productionMaterials as $material) {
        //     // Пошук матеріалу на складі з достатньою кількістю
        //     $warehouseMaterial = \App\Models\WarehouseMaterial::where('material_id', $material['material_id'])
        //         ->where('quantity', '>=', $material['quantity'])
        //         ->first();

        //     // Якщо матеріалу недостатньо, викидається виключення
        //     if (!$warehouseMaterial) {
        //         throw new \Exception("Недостатньо запасів для матеріалу з ID: {$material['material_id']}");
        //     }

        //     // Додавання вартості матеріалу до загальної вартості
        //     $rawMaterialCost += $warehouseMaterial->price * $material['quantity'];
        // }
        // // Розрахунок фінальної ціни продукту з націнкою 40%, враховуючи вартість роботи працівників
        // $productionStages = $this->data['production_stages'] ?? [];
        // $workerCost = 0;

        // foreach ($productionStages as $stage) {
        //     $workerCost += $stage['paid_worker'] ?? 0;
        // }


        // $this->data['price'] = ($rawMaterialCost + $workerCost) * 1.4;


        // // Якщо вибрано клієнта, створюється запис про розміри клієнта
        // if (!empty($this->data['customer_id'])) {
        //     \App\Models\CustomerSize::create([
        //         'customer_id' => $this->data['customer_id'],
        //         'throat' => $this->data['sizes']['throat'] ?? null, // Розмір горловини
        //         'redistribution' => $this->data['sizes']['redistribution'] ?? null, // Розподіл
        //         'behind' => $this->data['sizes']['behind'] ?? null, // Розмір спини
        //         'hips' => $this->data['sizes']['hips'] ?? null, // Розмір стегон
        //         'length' => $this->data['sizes']['length'] ?? null, // Довжина
        //         'sleeve' => $this->data['sizes']['sleeve'] ?? null, // Довжина рукава
        //         'shoulder' => $this->data['sizes']['shoulder'] ?? null, // Ширина плечей
        //         'comment' => $this->data['sizes']['comment'] ?? null, // Коментар
        //     ]);
        // }

        // $invoice = new Invoice();
        // // Створення накладної для виробництва
        // $invoice->type = 'переміщення';
        // $invoice->status = 'створено';
        // $invoice->payment_status = 'не оплачено';
        // $invoice->total = $this->data['price'];
        // $invoice->paid = 0;
        // $invoice->due = $this->data['price'];
        // $invoice->notes = 'Накладна для переміщення матеріалів на виробництво';
        // $invoice->save();

        // // Додавання позицій до накладної
        // foreach ($productionMaterials as $material) {
        //     $warehouseMaterial = \App\Models\WarehouseMaterial::where('material_id', $material['material_id'])
        //     ->where('quantity', '>=', $material['quantity'])
        //     ->first();

        //     // Зменшення кількості матеріалів на складі
        //     $warehouseMaterial->quantity -= $material['quantity'];
        //     $warehouseMaterial->save();

        //     // Додавання позиції до накладної
        //     $invoice->invoiceItems()->create([
        //         'material_id' => $material['material_id'],
        //         'quantity' => $material['quantity'],
        //         'price' => $warehouseMaterial->price,
        //         'total' => $warehouseMaterial->price * $material['quantity'],
        //         ]);
        // }


        // // Створення накладної на продаж
        // $saleInvoice = new Invoice();
        // $saleInvoice->type = 'продаж';
        // $saleInvoice->status = 'створено';
        // $saleInvoice->payment_status = 'не оплачено';
        // $saleInvoice->total = $this->data['price'];
        // $saleInvoice->paid = 0;
        // $saleInvoice->due = $this->data['price'];
        // $saleInvoice->notes = 'Накладна для продажу готового продукту';
        // $saleInvoice->save();

        // Створення запису про виробництво
       // parent::create($another);

        // $invoice->invoiceProductionItems()->create([
        //     'production_id'=>$this->id,
        //     'quantity'=>$this->data['quantity'],
        //     'price'=>
        // ]);


    }
}
