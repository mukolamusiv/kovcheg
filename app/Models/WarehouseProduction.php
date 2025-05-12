<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProduction extends Model
{



    protected $fillable = ['warehouse_id', 'production_id', 'quantity', 'price', 'description'];


    public function production()
    {
        return $this->belongsTo(Production::class);
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function getProductionNameAttribute()
    {
        return $this->production->name;
    }

    public function invoiceProductionItems()
    {
        return $this->hasMany(InvoiceProductionItem::class);
    }
}
