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
        $pay_chargable->__set('id', $this->partnerOrder->id);
        $pay_chargable->__set('type', 'order');
        $pay_chargable->__set('amount', (double)$this->partnerOrder->due);
        $pay_chargable->__set('completionClass', $this->isAdvancedPayment ? "AdvancedOrderComplete" : "OrderComplete");
        $pay_chargable->__set('user_id', $this->partnerOrder->order->customer_id);
        $pay_chargable->__set('user_type', "App\\Models\\Customer");
        return $pay_chargable;
    }
}