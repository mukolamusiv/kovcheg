<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Testing\Fluent\Concerns\Has;

class InvoiceProductionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['invoice_id', 'production_id', 'quantity', 'price', 'total'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function production()
    {
        return $this->belongsTo(Production::class);
    }


}
