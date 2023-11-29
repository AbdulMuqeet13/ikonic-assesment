<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $domain
 * @property string $display_name
 * @property string $turn_customers_into_affiliates
 * @property User $user
 * @property float $default_commission_rate
 */
class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'display_name',
        'user_id',
        'turn_customers_into_affiliates',
        'default_commission_rate'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function createMerchant(array $data)
    {
        return self::query()->create([
            'domain' => $data['domain'],
            'user_id' => $data['user_id'],
            'display_name' => $data['name'],
        ]);
    }

    public static function getMerchantByUserId(int $id): Model|\Illuminate\Database\Eloquent\Builder|null
    {
        return self::query()->where('user_id', $id)->first();
    }

    public static function getMerchantByDomain(string $domain): Model|\Illuminate\Database\Eloquent\Builder|null
    {
        return self::query()->where('domain', $domain)->first();
    }

    public static function updateMerchant(User $user, array $data): bool
    {
        $merchant = self::getMerchantByUserId($user->id);
        return $merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }

    public static function getMerchantByEmail(string $email): ?\Illuminate\Database\Eloquent\Model
    {
        $user = User::getUserByEmail($email);

        if ($user) {
            return $user->merchant;
        } else {
            // Handle the case where the user with the given email is not found.
            return null;
        }
    }
}
