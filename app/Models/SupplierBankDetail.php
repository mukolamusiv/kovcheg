<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierBankDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'bank_name',
        'bank_account',
        'bank_code',
        'bank_address',
        'bank_swift',
        'bank_iban',
        'bank_card_number',
        'bank_card_name'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


}
