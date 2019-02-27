<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\Complete\PaymentComplete;

class Payable extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    public $timestamps = false;

    public function getReadableTypeAttribute()
    {
        if ($this->type == 'partner_order') {
            return 'order';
        } else if ($this->type == 'wallet_recharge') {
            return 'recharge';
        } else if ($this->type == 'subscription_order') {
            return 'subscription_order';
        }
    }

    public function getCompletionClass(): PaymentComplete
    {
        $class_name = "Sheba\\Payment\\Complete\\";
        if ($this->completion_type == 'advanced_order') {
            $class_name .= 'AdvancedOrderComplete';
        } else if ($this->completion_type == 'wallet_recharge') {
            $class_name .= 'RechargeComplete';
        } else if ($this->completion_type == 'order') {
            $class_name .= 'OrderComplete';
        }
        return new $class_name();
    }

    public function user()
    {
        return $this->morphTo();
    }

    public function getPayableModel()
    {
        $model = "App\\Models\\";
        if ($this->type == 'partner_order') {
            $model .= 'PartnerOrder';
        } elseif ($this->type == 'subscription_order') {
            $model .= 'SubscriptionOrder';
        }
        return $model;

    }

}