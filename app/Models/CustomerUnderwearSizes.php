<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerUnderwearSizes extends Model
{

    protected $fillable = [
        'customer_id',
        'throat',
        'length',
        'chest_volume',
        'belly_volume',
        'shoulder',
        'sleeve_length',
        'back_width',
        'cuff',
        'fabric',
        'embroidery',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
