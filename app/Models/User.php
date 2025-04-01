<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_photo_path',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function account()
    {
        return $this->morphOne(Account::class, 'owner');
    }

    protected static function booted()
    {
        static::created(function ($customer) {
            $customer->account()->create([
                'name' => 'Рахунок працівника - ' . $customer->name,
                'description' => 'Фінансовий рахунок працівника',
                'account_type' => 'пасив',
                'account_category' => 'працівник',
                'currency' => 'UAH',
                'balance' => 0.00,
            ]);
        });
    }

    //транзації що здійснював працівник
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }


    //етапи що виконував працівник
    public function production_stages()
    {
        return $this->hasMany(ProductionStage::class);
    }

    //накладні що проводив працівник
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    //продукція що виробляв працівник
    public function production()
    {
        return $this->hasMany(Production::class);
    }


}
