<?php

namespace App\Models;

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

}
