<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Material;
use App\Models\WarehouseMaterial;
use Filament\Notifications\Notification;

class MoveInvoiceService
{
    private $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }


    public function cancelInvoice()
    {
        return \DB::transaction(function () {
            // Скасування накладної

            foreach ($this->invoice->invoiceItems as $item) {
                $this->cancelMaterialWarehouse($item->material_id, $this->invoice->warehouse_id, $item->quantity, $item->price);
            }
            foreach($this->invoice->invoiceProductionItems as $item){
                //$this->cancelMaterialWarehouse($item->production->material_id, $this->invoice->warehouse_id, $item->quantity);
            }
            $this->invoice->status = 'створено';
            $this->invoice->save();
            Notification::make()
                ->title('Накладна скасована!')
                ->body('Потрібно здіснити дії на складі чи касі згідно накладної')
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();
            return true;
        });
    }


    // проведення накладної на продаж
    public function moveInvoice()
    {
        //$production = $this->invoice->production;
        $invoice = $this->invoice;
        return \DB::transaction(function () use ($invoice) {
            // Перевірка валідності матеріалу в складі
            foreach ($invoice->invoiceItems as $item){
                //dd($item->material_id, $invoice->warehouse_id, $item);
                $this->validateMaterialWarehouse($item->material_id, $invoice->warehouse_id);
                $this->moveMaterialWarehouse($item->material_id, $invoice->warehouse_id, $item->quantity, $item->price);
            }
            foreach($invoice->invoiceProductionItems as $item){
                //$this->validateMaterialWarehouse($item->production->material_id, $invoice->warehouse_id);
                //$this->moveMaterialWarehouse($item->production->material_id, $invoice->warehouse_id, $item->quantity);
            }

            // // Проведення накладної
            $this->invoice->status = 'проведено';
            $this->invoice->due_date = now();
            $this->invoice->save();
            Notification::make()
                ->title('Накладна проведена!')
                ->body('Потрібно здіснити дії на складі чи касі згідно накладної')
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();
            return true;
        });
    }

    //Перевірка валідності накладної
    private function validateInvoice(): bool
    {
        // Implement the logic to validate the invoice
        return true;
    }

    //Перевірка валідності матеріалу в складі
    private function validateMaterial(int $materialId): Material
    {
        $material = \App\Models\Material::findOrFail($materialId);
            if (!$material) {
                throw new \Exception('Матеріал відсутній у базі');
            }
        return $material;
    }

    //Перевірка валідності матеріалу в складі
    private function validateMaterialWarehouse( $materialId,  $warehouseId): bool
    {
        $material = $this->validateMaterial($materialId);
        $warehouseMaterial = $material->getMaterialWarehouse($warehouseId);
        if (!$warehouseMaterial) {
            Notification::make()
                    ->title('Помилка проведення!')
                    ->body('Матеріал ' . $material->name . ' відсутній на складі ' . $warehouseId)
                    ->icon('heroicon-o-x-circle')
                    //->icon('heroicon-o-check-circle')
                    ->danger()
                    ->send();
                    return false;
            throw new \Exception('Матеріал відсутній на складі або у базі');
        }
        // Перевіряємо, чи є дублікат
        if ($warehouseMaterial->count() > 1) {
            // матеріал дублюється в одному складі
            $duplicates = $warehouseMaterial->get()->groupBy(function ($item) {
                return $item->warehouse_id . '-' . $item->material_id;
            })->filter(function ($group) {
                return $group->count() > 1;
            });
            // Проходимо по кожній групі дублікатів
            foreach ($duplicates as $duplicateGroup) {
               // $duplicateGroup = $duplicateGroup->first();
                // Отримуємо перший запис у групі
                $first = $duplicateGroup->first();
                // Рахуємо загальну кількість матеріалу в групі
                $totalQuantity = $duplicateGroup->sum('quantity');

                // Оновлюємо кількість у першому записі
                $first->quantity = $totalQuantity;
                $first->save();

                // Видаляємо всі інші записи в групі, крім першого
                $duplicateGroup->slice(1)->each(function ($duplicate) {
                    $duplicate->delete();
                });
            }
            $material = $first;
        }

        if($warehouseMaterial->count() < 1 and $this->invoice->type != 'постачання'){
            throw new \Exception('Матеріал відсутній на складі даному складі');
        }
        return true;
    }

    private function moveMaterialWarehouse(int $materialId, int $warehouseId, $quantity, $price): bool
    {
        $materialWarehouse = $this->getMaterialWarehouse($materialId, $warehouseId, $quantity, $price);
        if($this->invoice->warehouse_to_id != null){
            $materialWarehouseTo = $this->getMaterialWarehouse($materialId, $this->invoice->warehouse_to_id, $quantity, $price);
        }

        if($this->invoice->type == 'постачання'){
            if ($this->invoice->supplier_id == null) {
                Notification::make()
                    ->title('Помилка проведення!')
                    ->body('Постачальник не вказаний')
                    ->icon('heroicon-o-x-circle')
                    //->icon('heroicon-o-check-circle')
                    ->danger()
                    ->send();
                    return false;
                throw new \Exception('Постачальник не вказаний');
            }
            $materialWarehouse->quantity += $quantity;
            $this->upPrice($materialWarehouse, $price);
            $materialWarehouse->save();
        }
        if($this->invoice->type == 'продаж'){
            if ($this->invoice->customer_id == null) {
                Notification::make()
                    ->title('Помилка проведення!')
                    ->body('Отримувач не вказаний')
                    ->icon('heroicon-o-x-circle')
                    //->icon('heroicon-o-check-circle')
                    ->danger()
                    ->send();
                    return false;
                    throw new \Exception('Отримувач не вказаний');
                }
                //dd('asdasd');
            $materialWarehouse->quantity -= $quantity;
            $this->upPrice($materialWarehouse, $price);
            $materialWarehouse->save();
        }
        if($this->invoice->type == 'переміщення'){
            $materialWarehouse->quantity -= $quantity;
            $this->upPrice($materialWarehouse, $price);
            $materialWarehouse->save();

            $materialWarehouseTo->quantity += $quantity;
            $materialWarehouseTo->price = $materialWarehouse->price;
            $materialWarehouseTo->save();

        }
        if($this->invoice->type == 'повернення'){
            if ($this->invoice->customer_id == null) {
                throw new \Exception('Отримувач не вказаний');
            }
            $materialWarehouse->quantity += $quantity;
            $this->upPrice($materialWarehouse, $price);
            $materialWarehouse->save();
        }
        if($this->invoice->type == 'списання'){
            $materialWarehouse->quantity -= $quantity;
            $this->upPrice($materialWarehouse, $price);
            $materialWarehouse->save();
        }
        return true;
    }

    private function upPrice(WarehouseMaterial $materialWarehouse, $price): WarehouseMaterial
    {
        if($materialWarehouse->price < $price){
            $materialWarehouse->price = $price;
            $materialWarehouse->save();
            Notification::make()
                ->title('Ціна матеріалу змінена!')
                ->body('Ціна матеріалу ' . $materialWarehouse->material->name . ' змінена на ' . $price)
                ->icon('heroicon-o-check-circle')
                ->success()
                ->send();
        }
        $materialWarehouse->save();
        return $materialWarehouse;

    }

    private function getMaterialWarehouse(int $materialId, int $warehouseId, $quantity, $price): WarehouseMaterial
    {
        $warehouseMaterial = WarehouseMaterial::where('material_id', $materialId)
            ->where('warehouse_id', $warehouseId)
            ->first();
        if (!$warehouseMaterial) {
            if($this->invoice->type === 'постачання'){
                $material = Material::findOrFail($materialId);
                $warehouseMaterial = new WarehouseMaterial();
                $warehouseMaterial->material_id = $material->id;
                $warehouseMaterial->warehouse_id = $warehouseId;
                $warehouseMaterial->quantity = $quantity;
                $warehouseMaterial->price = $price;
                $warehouseMaterial->save();
                Notification::make()
                    ->title('Матеріл доданий до складу!')
                    ->body('Матеріал ' . $material->name . ' доданий до складу ')
                    ->icon('heroicon-o-check-circle')
                    ->success()
                    ->send();
                return $warehouseMaterial;
            }else{
                Notification::make()
                    ->title('Помилка проведення!')
                    ->body('Матеріал відсутній на складі')
                    ->icon('heroicon-o-x-circle')
                    //->icon('heroicon-o-check-circle')
                    ->danger()
                    ->send();
                    throw new \Exception('Матеріал відсутній на складі або у базі даних');
                }
        }else{
            return $warehouseMaterial;
        }
    }


    private function cancelMaterialWarehouse(int $materialId, int $warehouseId, $quantity, $price): bool
    {
        $materialWarehouse = $this->getMaterialWarehouse($materialId, $warehouseId, $quantity, $price);
        if ($this->invoice->type == 'постачання') {
           // dd('asdasd');
            $materialWarehouse->quantity -= $quantity;
            $materialWarehouse->save();
        }
        if ($this->invoice->type == 'продаж') {
            $materialWarehouse->quantity += $quantity;
            //dd($materialWarehouse,$quantity);
            $materialWarehouse->save();
        }
        if($this->invoice->type == 'повернення'){
           // dd('asdasd');
            if ($this->invoice->customer_id == null) {
                throw new \Exception('Отримувач не вказаний');
            }
            $materialWarehouse->quantity -= $quantity;
            $materialWarehouse->save();
        }
        if($this->invoice->type == 'списання'){
           // dd('asdasd');
            $materialWarehouse->quantity += $quantity;
            $materialWarehouse->save();
        }
        if($this->invoice->type == 'переміщення'){
            //dd('asdasd');
            $materialWarehouse->quantity -= $quantity;
            $materialWarehouse->save();
            if($this->invoice->warehouse_to_id != null){
                $materialWarehouseTo = $this->getMaterialWarehouse($materialId, $this->invoice->warehouse_to_id, $quantity, $price);
            }
            $materialWarehouseTo->quantity += $quantity;
            $materialWarehouseTo->price = $materialWarehouse->price;
            $materialWarehouseTo->save();
        }
        return true;
    }
}
