<?php namespace Sheba\Payment\Complete;

use App\Models\PartnerOrder;
use Sheba\JobDiscount\JobDiscountHandler;

abstract class BaseOrderComplete extends PaymentComplete
{
    protected $jobDiscountHandler;

    public function __construct(JobDiscountHandler $job_discount_handler)
    {
        parent::__construct();
        $this->jobDiscountHandler = $job_discount_handler;
    }

    abstract function giveOnlineDiscount(PartnerOrder $partner_order, $payment_method);
}