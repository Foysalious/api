<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\Payable;
use Carbon\Carbon;
use Sheba\CancelRequest\CancelRequestStatuses;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\Jobs\JobStatuses;
use Sheba\Payment\Adapters\Error\PayableInitiateErrorException;

class OrderAdapter implements PayableAdapter
{
    /** @var PartnerOrder $partnerOrder */
    private $partnerOrder;
    private $isAdvancedPayment;
    private $userId;
    private $userType;
    /** @var Job $job */
    private $job;
    private $emiMonth;
    private $paymentMethod;

    public function __construct()
    {
        $this->isAdvancedPayment = 0;
        $this->emiMonth = null;
    }

    /**
     * @param PartnerOrder $partnerOrder
     * @return OrderAdapter
     */
    public function setPartnerOrder($partnerOrder)
    {
        $this->partnerOrder = $partnerOrder;
        $this->partnerOrder->calculate(true);
        $this->setJob($this->partnerOrder->getActiveJob());
        $this->setUser();
        return $this;
    }

    /**
     * @param bool $isAdvancedPayment
     * @return OrderAdapter
     */
    public function setIsAdvancedPayment($isAdvancedPayment)
    {
        $this->isAdvancedPayment = $isAdvancedPayment;
        return $this;
    }

    /**
     * @param $month |int
     * @return $this
     */
    public function setEmiMonth($month)
    {
        $this->emiMonth = (int)$month;
        return $this;
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        return $this;
    }

    private function setJob($job)
    {
        $this->job = $job;
        return $this;
    }

    public function getPayable($amount = 0): Payable
    {
        if (!$this->canInit()) throw new PayableInitiateErrorException('Payable can not be initiated');
        $payable = new Payable();
        $payable->type = 'partner_order';
        $payable->type_id = $this->partnerOrder->id;
        $payable->user_id = $this->userId;
        $payable->user_type = $this->userType;
        $due = $this->getDue();
        $amount = ($amount === 0 || $this->resolveEmiMonth($due)) ? $due : $amount;
        $payable->amount = $this->calculateAmount($amount);
        $payable->emi_month = $this->resolveEmiMonth($due);
        $payable->completion_type = $this->isAdvancedPayment ? 'advanced_order' : "order";
        $payable->success_url = $this->getSuccessUrl();
        $payable->fail_url = $this->getFailUrl();
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
    }

    public function getDue()
    {
        return (double)$this->partnerOrder->dueWithLogisticWithoutRoundingCutoff;
    }

    private function calculateAmount($due)
    {
        if ($this->job->isOnlinePaymentDiscountApplicable()) {
            $due -= $this->discountedAmount();
        }
        return floor($due);
    }

    private function discountedAmount()
    {
        $category_id = $this->job->jobServices->first()->service->category_id;
        $discount_checking_params = (new JobDiscountCheckingParams())
            ->setDiscountableAmount($this->partnerOrder->due)
            ->setOrderAmount($this->partnerOrder->grossAmount)
            ->setPaymentGateway($this->paymentMethod);
        $job_discount_handler = app(JobDiscountHandler::class);

        $job_discount_handler->setType(DiscountTypes::ONLINE_PAYMENT)
            ->setCheckingParams($discount_checking_params)->calculate();

        if ($job_discount_handler->hasDiscount($category_id)) {
            return $job_discount_handler->getApplicableAmount();
        }
        return 0;
    }

    private function setUser()
    {
        $order = $this->partnerOrder->order;

        if ($order->payer_type == 'affiliate' && $order->payer_id > 0) {
            $this->userId = $order->payer_id;
            $this->userType = "App\\Models\\Affiliate";
        } elseif ($order->partner_id) {
            $this->userId = $order->partner_id;
            $this->userType = "App\\Models\\Partner";
        } elseif ($order->business_id) {
            $this->userId = $order->business_id;
            $this->userType = "App\\Models\\Business";
        } else {
            $this->userId = $order->customer_id;
            $this->userType = "App\\Models\\Customer";
        }
    }

    private function getSuccessUrl()
    {
        if ($this->userType == "App\\Models\\Business") return config('sheba.business_url') . "/dashboard/orders/quick-purchase/" . $this->partnerOrder->id;
        else return config('sheba.front_url') . '/orders/' . $this->partnerOrder->getActiveJob()->id . '/payment';
    }

    private function getFailUrl()
    {
        if ($this->userType == "App\\Models\\Business") return config('sheba.business_url') . "/dashboard/orders/quick-purchase/" . $this->partnerOrder->id;
        else return config('sheba.front_url') . '/orders/' . $this->partnerOrder->getActiveJob()->id . '/payment';
    }

    private function resolveEmiMonth($amount)
    {
        return $amount >= config('sheba.min_order_amount_for_emi') ? $this->emiMonth : null;
    }

    public function setModelForPayable($model)
    {
        // TODO: Implement setModelForPayable() method.
    }

    public function canInit(): bool
    {
        if ((double)$this->partnerOrder->getCustomerPayable() <= 0) return false;
        if ($this->partnerOrder->isCancelled()) return false;
        if (in_array($this->job->status, [JobStatuses::DECLINED]) || $this->job->hasPendingCancelRequest()) return false;
        return true;
    }
}
