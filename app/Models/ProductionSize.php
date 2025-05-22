<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionSize extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'production_id',
        'throat',
        'redistribution',
        'behind',
        'hips',
        'length',
        'sleeve',
        'shoulder',
        'comment',
        'neck',
        'front',
        'epitrachelion',
        'abdomen_volume',
        'height',
        'floor_height',
        'chest_volume',
        'cuffs',
        'awards',
        'sticharion',
    ];

}
