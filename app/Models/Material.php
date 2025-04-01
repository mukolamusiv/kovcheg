<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;
   // protected $table = 'materials';
    protected $fillable = ['name', 'description','image','unit' , 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouses()
    {
        return $this->hasMany(WarehouseMaterial::class);
    }

    public function getStockInWarehouse($warehouseId)
    {
        return $this->warehouses()->where('warehouse_id', $warehouseId)->sum('quantity');
    }

    public function getTotalValueInWarehouse($warehouseId)
    {
        return $this->warehouses()
                    ->where('warehouse_id', $warehouseId)
                    ->get()
                    ->sum(function ($warehouseMaterial) {
                        return $warehouseMaterial->price;
                    });
    }

}
