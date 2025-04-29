<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateProductionsStage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'template_productions_id',
        'name',
        'description',
        'paid_worker',
        'user_id',
    ];


    public function templateProduction()
    {
        return $this->belongsTo(TemplateProduction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
