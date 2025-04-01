<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerSize extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = ['customer_id', 'throat', 'redistribution', 'behind', 'hips','length','sleeve','shoulder','comment'];


    public function custumer()
    {
        $this->belongsTo(Customer::class);
    }

}
