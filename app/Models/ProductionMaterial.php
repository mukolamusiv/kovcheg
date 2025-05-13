<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'production_id',
        'material_id',
        'quantity',
        'price',
        'description',
        'date_writing_off',
        'invoice_id',
        'warehouse_id',
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }


    public function getStockInWarehouse()
    {
        return $this->material->warehouses()
            ->where('warehouse_id', $this->warehouse_id)
            ->sum('quantity');
    }

    public function checkStockInWarehouse()
    {
        if ($this->quantity > $this->getStockInWarehouse()) {
            Notification::make()
                ->title('Матеріалу '.$this->material->name.' не достатньо на складі')
                ->danger()
                ->send();
            return false;
        }else{
            return true;
        }
    }

    protected static function booted()
    {
        // Подія перед створенням накладної
        static::creating(function ($productionMaterial) {
            if($productionMaterial->material->checkMaterialInWarehouse($productionMaterial->warehouse_id)){
                $productionMaterial->price = $productionMaterial->material->getPriceMaterial($productionMaterial->warehouse_id)->price;
            }else{
                $productionMaterial->price = 0;
            }
        });

        static::updating(function($productionMaterial){
            if($productionMaterial->material->checkMaterialInWarehouse($productionMaterial->warehouse_id)){
               // dd($productionMaterial,$productionMaterial->material->getPriceMaterial($productionMaterial->warehouse_id),$productionMaterial->material->getPriceMaterial($productionMaterial->warehouse_id)->price);
                $productionMaterial->price = $productionMaterial->material->getPriceMaterial($productionMaterial->warehouse_id)->price;
            }else{
               // $productionMaterial->price = $productionMaterial->material->getPriceMaterial($productionMaterial->warehouse_id)->price;
               $warehouse = $productionMaterial->material->getPricesMaterial();
                dd($productionMaterial->material->getPricesMaterial($productionMaterial->warehouse_id), $productionMaterial->material, $productionMaterial);
            }
        });
    }
}
