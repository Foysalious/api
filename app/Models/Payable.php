<?php namespace App\Models;

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
        } else if ($this->type == 'gift_card_purchase') {
            return 'gift_card_purchase';
        } else if ($this->type == 'movie_ticket_purchase') {
            return 'movie_ticket_purchase';
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
        } else if ($this->completion_type == 'gift_card_purchase') {
            $class_name .= 'GiftCardPurchaseComplete';
        } else if ($this->completion_type == 'movie_ticket_purchase') {
            $class_name .= 'MovieTicketPurchaseComplete';
        }

        return new $class_name();
    }

    public function user()
    {
        return $this->morphTo();
    }

    public function getMobile()
    {
        if ($this->user instanceof Customer) {
            return $this->user->profile->mobile;
        } elseif ($this->user instanceof Business) {
            return $this->user->mobile;
        }
    }

    public function getEmail()
    {
        if ($this->user instanceof Customer) {
            return $this->user->profile->email;
        } elseif ($this->user instanceof Business) {
            return $this->user->email;
        }
    }

    public function getName()
    {
        if ($this->user instanceof Customer) {
            return $this->user->profile->name;
        } elseif ($this->user instanceof Business) {
            return $this->user->name;
        }
    }


    public function getPayableModel()
    {
        $model = "App\\Models\\";
        if ($this->type == 'partner_order') {
            $model .= 'PartnerOrder';
        } elseif ($this->type == 'subscription_order') {
            $model .= 'SubscriptionOrder';
        } elseif ($this->type == 'gift_card_purchase') {
            $model .= 'GiftCardPurchase';
        } else {
            $model .= 'MovieTicketOrder';
        }
        return $model;
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}