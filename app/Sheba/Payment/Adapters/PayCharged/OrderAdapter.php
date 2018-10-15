<?php
/**
 * Created by PhpStorm.
 * User: arnab
 * Date: 9/11/18
 * Time: 8:36 AM
 */

namespace Sheba\Payment\Adapters\PayCharged;


use App\Models\PartnerOrder;
use Sheba\Payment\PayCharged;

class OrderAdapter implements PayChargedAdapter
{
    private $partnerOrder;
    private $transactionId;

    public function __construct(PartnerOrder $partner_order, $transaction_id)
    {
        $this->partnerOrder = $partner_order;
        $this->transactionId = $transaction_id;
    }

    public function getPayCharged(): PayCharged
    {
        $pay_charged = new PayCharged();
        $pay_charged->id = $this->partnerOrder->id;
        $pay_charged->type = 'order';
        $pay_charged->userId = $this->partnerOrder->order->customer_id;
        $pay_charged->userType = "App\\Models\\Customer";
        $pay_charged->transactionId = $this->transactionId;
        return $pay_charged;
    }
}