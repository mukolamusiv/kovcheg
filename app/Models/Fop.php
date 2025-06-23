<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fop extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'iban',
        'bank_name',
        'bank_code',
        'ipn',
    ];

    protected $casts = [
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',
        'iban' => 'string',
        'bank_name' => 'string',
        'bank_code' => 'string',
    ];


    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'fop_id', 'id');
    }
}
