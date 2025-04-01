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

    public function warehouses()
    {
        return $this->hasMany(WarehouseMaterial::class);
    }

    public function getStockInWarehouse($warehouseId)
    {
        return $this->warehouses()->where('warehouse_id', $warehouseId)->sum('quantity');
    }

    public function getTotalValueInWarehouse($warehouseId)
    {
        return $this->warehouses()
                    ->where('warehouse_id', $warehouseId)
                    ->get()
                    ->sum(function ($warehouseMaterial) {
                        return $warehouseMaterial->price;
                    });
    }

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
        // Генеруємо випадкову числову частину штрихкоду з 11 цифр.
        $randomNumber = str_pad(mt_rand(0, 99999999999), 11, '0', STR_PAD_LEFT);

        // Обчислюємо контрольну цифру за допомогою алгоритму Луна для перевірки помилок.
        $checksum = self::calculateLuhnChecksum($randomNumber);

        // Додаємо контрольну цифру до базового штрихкоду.
        $barcode = $randomNumber . $checksum;

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
        $sum = 0;
        $isSecond = false;

        // Обробляємо кожну цифру з правого боку до лівого.
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];

            if ($isSecond) {
                // Подвоюємо кожну другу цифру.
                $digit *= 2;

                // Якщо результат більше 9, віднімаємо 9.
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $isSecond = !$isSecond;
        }

        // Контрольна цифра — це число, необхідне для того, щоб сума стала кратною 10.
        return (10 - ($sum % 10)) % 10;
    }
}
