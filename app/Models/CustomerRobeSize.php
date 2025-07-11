<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRobeSize extends Model
{

    protected $fillable = [
        'customer_id',
        'throat',
        'front',
        'front_type',
        'back',
        'epitrachelion_length',
        'epitrachelion_type',
        'cuff_type',
        'awards',
        'tape',
        'clasp',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
