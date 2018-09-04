<?php

namespace Sheba\PayCharge\Adapters;


use App\Models\PartnerOrder;
use Sheba\PayCharge\PayChargable;

class OrderAdapter implements PayChargableAdapter
{
    private $partnerOrder;
    private $isAdvancedPayment;

    public function __construct(PartnerOrder $partner_order, $is_advanced_payment = false)
    {
        $this->partnerOrder = $partner_order;
        $this->partnerOrder->calculate(true);
        $this->isAdvancedPayment = $is_advanced_payment;
    }

    public function getPayable(): PayChargable
    {
        $pay_chargable = new PayChargable();
        $pay_chargable->id = $this->partnerOrder->id;
        $pay_chargable->type = 'order';
        $pay_chargable->amount = (double)$this->partnerOrder->due;
        $pay_chargable->completionClass = $this->isAdvancedPayment ? "AdvancedOrderComplete" : "OrderComplete";
        $pay_chargable->user_id = $this->partnerOrder->order->customer_id;
        $pay_chargable->user_type = "App\\Models\\Customer";
        return $pay_chargable;
    }
}