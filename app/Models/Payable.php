<?php namespace App\Models;

use App\Sheba\PaymentLink\PaymentLinkOrder;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Payable\Types;
use Sheba\Payment\Complete\PaymentComplete;
use Sheba\Payment\PayableType;
use Sheba\Utility\UtilityOrder;

class Payable extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    public $timestamps = false;

    public function getReadableTypeAttribute()
    {
        if ($this->type == Types::PARTNER_ORDER) {
            return 'order';
        } else if ($this->type == Types::WALLET_RECHARGE) {
            return 'recharge';
        } else if ($this->type == Types::SUBSCRIPTION_ORDER) {
            return 'subscription_order';
        } else if ($this->type == Types::GIFT_CARD_PURCHASE) {
            return 'gift_card_purchase';
        } else if ($this->type == Types::MOVIE_TICKET_PURCHASE) {
            return 'movie_ticket_purchase';
        } else if ($this->type == Types::TRANSPORT_TICKET_PURCHASE) {
            return 'transport_ticket_purchase';
        } else if ($this->type == Types::UTILITY_ORDER) {
            return 'utility_order';
        } else if ($this->type == Types::PAYMENT_LINK) {
            return 'payment_link';
        } else if ($this->type == Types::PROCUREMENT) {
            return 'procurement';
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
        } else if ($this->completion_type == 'subscription_order') {
            $class_name .= 'SubscriptionOrderComplete';
        } else if ($this->completion_type == 'gift_card_purchase') {
            $class_name .= 'GiftCardPurchaseComplete';
        } else if ($this->completion_type == 'movie_ticket_purchase') {
            $class_name .= 'MovieTicketPurchaseComplete';
        } else if ($this->completion_type == 'transport_ticket_purchase') {
            $class_name .= 'TransportTicketPurchaseComplete';
        } else if ($this->completion_type == 'utility_order') {
            $class_name .= 'UtilityOrderComplete';
        } else if ($this->completion_type == 'payment_link') {
            $class_name .= 'PaymentLinkOrderComplete';
        } else if ($this->completion_type == 'procurement') {
            $class_name .= 'ProcurementComplete';
        }

        return app($class_name);
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
        } elseif ($this->user instanceof Partner) {
            return $this->user->mobile;
        }
    }

    public function getUserProfile()
    {
        if ($this->user instanceof Customer) {
            return $this->user->profile;
        } elseif ($this->user instanceof Business || $this->user instanceof Partner) {
            return $this->user->getAdmin()->profile;
        } else {
            return new Profile();
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
        } elseif ($this->user instanceof Partner) {
            return $this->user->name;
        }
    }


    public function getPayableModel()
    {
        $model = "App\\Models\\";
        if ($this->type == Types::PARTNER_ORDER) {
            $model .= 'PartnerOrder';
        } elseif ($this->type == Types::SUBSCRIPTION_ORDER) {
            $model .= 'SubscriptionOrder';
        } elseif ($this->type == Types::GIFT_CARD_PURCHASE) {
            $model .= 'GiftCardPurchase';
        } elseif ($this->type == Types::MOVIE_TICKET_PURCHASE) {
            $model .= 'MovieTicketOrder';
        } elseif ($this->type == Types::TRANSPORT_TICKET_PURCHASE) {
            $model .= "Transport\\TransportTicketOrder";
        } elseif ($this->type == Types::PROCUREMENT) {
            $model .= "Procurement";
        }

        return $model;
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * @return PayableType
     */
    public function getPayableType()
    {
        if ($this->type == Types::UTILITY_ORDER) {
            return (new UtilityOrder())->setPayable($this);
        } elseif ($this->type == Types::PAYMENT_LINK) {
            return (new PaymentLinkOrder())->setPayable($this);
        } else {
            return ($this->getPayableModel())::find($this->type_id);
        }
    }
}
