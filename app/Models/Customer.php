<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Sheba\Voucher\VoucherCodeGenerator;

class Customer extends Authenticatable
{
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'fb_id',
        'mobile_verified',
        'email_verified',
        'address',
        'gender',
        'dob',
        'pro_pic',
        'created_by',
        'created_by_name',
        'updated_by',
        'updated_by_name',
        'remember_token',
        'reference_code', 'referrer_id', 'profile_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function mobiles()
    {
        return $this->hasMany(CustomerMobile::class);
    }

    public function delivery_addresses()
    {
        return $this->hasMany(CustomerDeliveryAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function custom_orders()
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function suggestedPromotion()
    {
        return suggestedVoucherFor($this);
    }

    public function generateReferral()
    {
        return VoucherCodeGenerator::byName($this->profile->name);
    }

    public function vouchers()
    {
        return $this->morphMany(Voucher::class, 'owner');
    }

    public function usedVouchers()
    {
        return $this->orders()->where('voucher_id', '<>', null)->get()->map(function($order) { return $order->voucher; })->unique();
    }


    public function nthOrders(...$n)
    {
        if(!count($n)) throw new \InvalidArgumentException('n is not valid.');
        if(is_array($n[0])) $n = array_pop($n);

        $counter = 0;

        return $this->orders()->orderBy('id')->get()->filter(function($order) use (&$counter, $n) {
            $counter++;
            return in_array($counter, $n);
        });
    }

    public function getReferralAttribute()
    {
        $vouchers = $this->vouchers;
        return $vouchers ? $vouchers->first() : null;
    }

    public function getIdentityAttribute()
    {
        if ($this->profile->name != '') {
            return $this->profile->name;
        } elseif ($this->profile->mobile) {
            return $this->profile->mobile;
        }
        return $this->profile->email;
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
}
