<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property User $user
 * @property Merchant $merchant
 * @property float $commission_rate
 */
class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'merchant_id',
        'commission_rate',
        'discount_code'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public static function createAffiliate(array $data): Model|\Illuminate\Database\Eloquent\Builder
    {
        return self::query()->create($data);
    }

    public static function getAffiliateByEmail(string $email): Model|\Illuminate\Database\Eloquent\Builder|null
    {
        return self::query()->whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->first();
    }

    public static function getAffiliateByDiscountCode(string $discountCode): Model|\Illuminate\Database\Eloquent\Builder|null
    {
        return self::query()->where('discount_code', $discountCode)->first();
    }
}
