<?php namespace Sheba\Payment\Complete;

use App\Models\PartnerOrder;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\ModificationFields;
use Sheba\Payment\Factory\PaymentStrategy;

abstract class BaseOrderComplete extends PaymentComplete
{
    use ModificationFields;

    protected $jobDiscountHandler;

    public function __construct(JobDiscountHandler $job_discount_handler)
    {
        parent::__construct();
        $this->jobDiscountHandler = $job_discount_handler;
    }

    /**
     * @param PartnerOrder $partner_order
     * @throws InvalidDiscountType
     */
    public function giveOnlineDiscount(PartnerOrder $partner_order)
    {
        $partner_order->calculate(true);
        $job = $partner_order->getActiveJob();
        $category_id = $job->jobServices->first()->service->category_id;

        if ($job->isOnlinePaymentDiscountApplicable()) {
            $payment_gateway = $this->payment->paymentDetails[0]->method;
            if ($payment_gateway == 'ssl') $payment_gateway = PaymentStrategy::ONLINE;
            $discount_checking_params = (new JobDiscountCheckingParams())
                ->setDiscountableAmount($partner_order->due)
                ->setOrderAmount($partner_order->grossAmount)
                ->setPaymentGateway($payment_gateway);

            $this->jobDiscountHandler->setType(DiscountTypes::ONLINE_PAYMENT)
                ->setCheckingParams($discount_checking_params)->calculate();

            if ($this->jobDiscountHandler->hasDiscount($category_id)) {
                $this->jobDiscountHandler->create($job);
                $job->discount += $this->jobDiscountHandler->getApplicableAmount();
                $job->update();
            }
        }
    }
}
