<?php namespace App\Models;

use App\Sheba\PaymentLink\PaymentLinkOrder;
use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\Complete\PaymentComplete;
use Sheba\Payment\PayableType;
use Sheba\Utility\UtilityOrder;

class Payable extends Model {
    protected $guarded    = ['id'];
    protected $casts      = ['amount' => 'double'];
    public    $timestamps = false;

    public function getReadableTypeAttribute() {
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
        } else if ($this->type == 'transport_ticket_purchase') {
            return 'transport_ticket_purchase';
        } else if ($this->type == 'utility_order') {
            return 'utility_order';
        } else if ($this->type == 'payment_link') {
            return 'payment_link';
        } else if ($this->type == 'procurement') {
            return 'procurement';
        }
    }

    public function getCompletionClass(): PaymentComplete {
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

    public function user() {
        return $this->morphTo();
    }

    public function getMobile() {
        if ($this->user instanceof Customer) {
            return $this->user->profile->mobile;
        } elseif ($this->user instanceof Business) {
            return $this->user->mobile;
        } elseif ($this->user instanceof Partner) {
            return $this->user->mobile;
        }
    }

    public function getUserProfile() {
        if ($this->user instanceof Customer) {
            return $this->user->profile;
        } elseif ($this->user instanceof Business || $this->user instanceof Partner) {
            return $this->user->getAdmin()->profile;
        } else {
            return new Profile();
        }
    }

    public function getEmail() {
        if ($this->user instanceof Customer) {
            return $this->user->profile->email;
        } elseif ($this->user instanceof Business) {
            return $this->user->email;
        }
    }

    public function getName() {
        if ($this->user instanceof Customer) {
            return $this->user->profile->name;
        } elseif ($this->user instanceof Business) {
            return $this->user->name;
        } elseif ($this->user instanceof Partner) {
            return $this->user->name;
        }
    }


    public function getPayableModel() {
        $model = "App\\Models\\";
        if ($this->type == 'partner_order') {
            $model .= 'PartnerOrder';
        } elseif ($this->type == 'subscription_order') {
            $model .= 'SubscriptionOrder';
        } elseif ($this->type == 'gift_card_purchase') {
            $model .= 'GiftCardPurchase';
        } elseif ($this->type == 'movie_ticket_purchase') {
            $model .= 'MovieTicketOrder';
        } elseif ($this->type == 'transport_ticket_purchase') {
            $model .= "Transport\\TransportTicketOrder";
        } elseif ($this->type == 'procurement') {
            $model .= "Procurement";
        }

        return $model;
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }

    /**
     * @return PayableType
     */
    public function getPayableType() {
        if ($this->type == 'utility_order') {
            return (new UtilityOrder())->setPayable($this);
        } elseif ($this->type == 'payment_link') {
            return (new PaymentLinkOrder())->setPayable($this);
        } else {
            return ($this->getPayableModel())::find($this->type_id);
        }
    }
}
