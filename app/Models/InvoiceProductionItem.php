<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Testing\Fluent\Concerns\Has;

class InvoiceProductionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['invoice_id', 'warehouse_productions_id', 'production_id', 'quantity', 'price', 'total'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // public function production()
    // {
    //     return $this->hasOneThrough(
    //         Production::class,
    //         WarehouseProduction::class,
    //         'id', // Foreign key on the warehouse_productions table
    //         'id', // Foreign key on the productions table
    //         'warehouse_productions_id', // Local key on the invoice_production_items table
    //         'production_id' // Local key on the warehouse_productions table
    //     );
    // }

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function warehouseProduction()
    {
        return $this->belongsTo(WarehouseProduction::class);
    }


}
