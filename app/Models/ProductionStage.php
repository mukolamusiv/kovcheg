<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionStage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'production_id',
        'user_id',
        'date',
        'paid_worker'
    ];


    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    protected static function booted()
    {
        static::creating(function ($stage) {
           //if(!empty( $stage->user_id)){
                $stage->user_id = auth()->id();
           //}
        });
    }


}
