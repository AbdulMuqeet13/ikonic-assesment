<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Cassandra\Type\UserType;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method

        // Check if the email is already associated with a merchant or an affiliate
        $existingAffiliate = Affiliate::getAffiliateByEmail($email);
        if ($existingAffiliate) {
            throw new AffiliateCreateException('Email is already in use as an affiliate.');
        }

        if ($merchant->user->email === $email) {
            throw new AffiliateCreateException('Email is already in use as a merchant.');
        }


        // Create a new user
        $user = [
            'email' => $email,
            'name' => $name,
            'type' => User::TYPE_AFFILIATE
        ];
        $user = User::createUser($user);

        // Create a discount code using the ApiService
        $discountCodeResponse = $this->apiService->createDiscountCode($merchant);
        $discountCode = $discountCodeResponse['code'];

        // Create a new affiliate
        $affiliate = [
            'merchant_id' => $merchant->id,
            'user_id' => $user->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode,
            // You may add other affiliate properties as needed
        ];
        $affiliate = Affiliate::createAffiliate($affiliate);

        // Send an email notification
        Mail::to($email)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
}
