<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'address', 'description'];

   // матеріали на складі
    public function materials()
    {
        return $this->hasMany(WarehouseMaterial::class);
    }

    public function productionMaterials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    public function warehouseMaterials()
    {
        return $this->hasMany(WarehouseMaterial::class);
    }

    public function warehouseMaterial($materialId)
    {
        return $this->hasMany(WarehouseMaterial::class)->where('material_id', $materialId)->last();
    }

    public function account()
    {
        return $this->morphOne(Account::class, 'owner');
    }


    protected static function booted()
    {
        static::created(function ($warehouse) {
            $warehouse->account()->create([
                'name' => 'Технічний рахунок складу - ' . $warehouse->name,
                'description' => 'Загальний баланс та розрахунок матеріалів що знаходяться на складі',
                'account_type' => 'актив',
                'account_category' => 'інше',
                'currency' => 'UAH',
                'balance' => 0.00,
            ]);
        });
    }


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function ($model) {
    //         // Validate data before saving
    //         if ($model->amount <= 0) {
    //             throw new \Exception('Amount must be greater than zero.');
    //         }

    //         // Update account balance
    //         $account = $model->account;
    //         if ($model->entry_type == 'credit') {
    //             $account->balance += $model->amount;
    //         } elseif ($model->entry_type == 'debit') {
    //             $account->balance -= $model->amount;
    //         }
    //         dd($account->balance);
    //         $account->save();
    //     });
    // }

}
