<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateProduction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'description',
        'image',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(TemplateProductionsStage::class, 'template_productions_id');
    }

    public function materials()
    {
        return $this->hasMany(TemplateProductionsMaterial::class, 'template_productions_id');
    }
}
