<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateProductionsMaterial extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'template_productions_id',
        'material_id',
        'quantity',
        'description',
        'warehouse_id',
    ];


    public function templateProduction()
    {
        return $this->belongsTo(TemplateProduction::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
