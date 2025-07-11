<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDalmaticSizes extends Model
{

    protected $fillable = [
        'customer_id',
        'throat',
        'length',
        'width',
        'sleeve_type',
        'sleeve_length',
        'shoulder',
        'stand_collar_zip',
        'fabric',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
