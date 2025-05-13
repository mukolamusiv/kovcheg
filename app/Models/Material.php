<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;
   // protected $table = 'materials';
    protected $fillable = [
        'name',
        'description',
        'image',
        'unit',
        'category_id',
        'barcode',
        'manufacturer_code',
        'supplier_id',
        'fabric_color'
    ];


    protected static function booted()
    {
        // Подія перед створенням накладної
        static::creating(function ($material) {
            $material->barcode = self::generateBarcode();
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Визначаємо зв'язок з таблицею warehouse_materials
    public function warehouses()
    {
        return $this->hasMany(WarehouseMaterial::class);
    }

    // Отримуємо загальну кількість матеріалів на складі
    public function getStockInWarehouse($warehouseId)
    {
        return $this->warehouses()->where('warehouse_id', $warehouseId)->sum('quantity');
    }

    // public function getMaterialWarehouse($warehouseId)
    // {
    //     return $this->warehouses()->where('warehouse_id', $warehouseId)->first();
    // }

    // Отримуємо загальну вартість матеріалів на складі
    public function getTotalValueInWarehouse($warehouseId)
    {
        return $this->warehouses()
                    ->where('warehouse_id', $warehouseId)
                    ->get()
                    ->sum(function ($warehouseMaterial) {
                        return $warehouseMaterial->price;
                    });
    }

    public function getPriceMaterial($warehouseId)
    {
        return $this->warehouses()->where('warehouse_id', $warehouseId)->first();
    }

    public function getPricesMaterial()
    {
        return $this->warehouses();
    }

    //первіркка наявності матеріалу на складі
    public function checkMaterialInWarehouse($warehouseId)
    {
        return $this->warehouses()->where('warehouse_id', $warehouseId)->exists();
    }

    public function getMaterialWarehouse($warehouseId)
    {
        return $this->warehouses()->where('warehouse_id', $warehouseId);
    }

    // public function findMaterialWarehouse($warehouseId)
    // {
    //     return $this->warehouses()->where('warehouse_id', $warehouseId);
    // }

    // Визначаємо зв'язок з таблицею suppliers
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Генерує унікальний штрихкод для матеріалу.
     *
     * @return string
     */
    public static function generateBarcode()
    {
        $barcodeUa = 482; // Базовий штрихкод для України
        $barcodeFactory = 7201; // Фабричний код
        // Генеруємо випадкову числову частину штрихкоду з 5 цифр.
        $randomNumber = str_pad(mt_rand(0, 9999), 5, '0', STR_PAD_LEFT);

        $barcode = $barcodeUa . $barcodeFactory . $randomNumber;
        // Обчислюємо контрольну цифру за допомогою алгоритму Луна для перевірки помилок.
        $checksum = self::calculateLuhnChecksum($barcode);

        // Додаємо контрольну цифру до базового штрихкоду.
        $barcode = $barcode . $checksum;

        // Перевіряємо, чи є штрихкод унікальним у базі даних.
        if (self::where('barcode', $barcode)->exists()) {
            // Якщо не унікальний, рекурсивно генеруємо новий штрихкод.
            return self::generateBarcode();
        }

        return $barcode;
    }

    /**
     * Обчислює контрольну цифру за алгоритмом Луна для заданого рядка.
     *
     * @param string $number
     * @return int
     */
    protected static function calculateLuhnChecksum($number)
    {
        $digits = str_split($number);
        $evenSum = 0;
        $oddSum = 0;

        foreach ($digits as $index => $digit) {
            if (($index + 1) % 2 === 0) {
            // Парні позиції (індексація починається з 0, тому +1)
            $evenSum += $digit;
            } else {
            // Непарні позиції
            $oddSum += $digit;
            }
        }

        // Множимо суму парних позицій на 3
        $evenSum *= 3;

        // Складаємо суми парних і непарних позицій
        $totalSum = $evenSum + $oddSum;

        // Відкидаємо десятки
        $remainder = $totalSum % 10;

        // Віднімаємо з 10
        $checksum = $remainder === 0 ? 0 : 10 - $remainder;

        return $checksum;
    }
}
