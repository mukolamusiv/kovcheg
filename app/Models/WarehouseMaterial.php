<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['warehouse_id', 'material_id', 'quantity', 'price', 'description'];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getMaterialNameAttribute()
    {
        return $this->material->name;
    }

    public function getMaterialUnitAttribute()
    {
        return $this->material->unit;
    }

    public function getMaterialCategoryAttribute()
    {
        return $this->material->category->name;
    }


}
