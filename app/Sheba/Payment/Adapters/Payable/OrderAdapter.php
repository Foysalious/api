<?php

namespace Sheba\Payment\Adapters\Payable;

use App\Models\PartnerOrder;
use App\Models\Payable;
use Sheba\Payment\PayChargable;

class OrderAdapter implements PayableAdapter
{
    private $partnerOrder;
    private $isAdvancedPayment;

    public function __construct(PartnerOrder $partner_order, $is_advanced_payment = false)
    {
        $this->partnerOrder = $partner_order;
        $this->partnerOrder->calculate(true);
        $this->isAdvancedPayment = $is_advanced_payment;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'partner_order';
        $payable->type_id = $this->partnerOrder->id;
        $payable->user_id = $this->partnerOrder->order->customer_id;
        $payable->user_type = "App\\Models\\Customer";
        $payable->amount = (double)$this->partnerOrder->due;
        $payable->completion_type = $this->isAdvancedPayment ? 'advanced_order' : "order";
        $payable->success_url = config('sheba.front_url') . '/orders/' . $this->partnerOrder->jobs()->where('status', '<>', constants('JOB_STATUSES')['Cancelled'])->first()->id;
        $payable->save();
        return $payable;
    }
}