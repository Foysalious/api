<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\Payable;
use Carbon\Carbon;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;

class OrderAdapter extends BaseAdapter implements PayableAdapter
{
    /** @var PartnerOrder $partnerOrder */
    private $partnerOrder;
    private $isAdvancedPayment;
    private $userId;
    private $userType;
    /** @var Job $job */
    private $job;
    private $emiMonth;

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

    public function getPayable(): Payable
    {
        $this->job = $this->partnerOrder->getActiveJob();
        $payable = new Payable();
        $payable->type = 'partner_order';
        $payable->type_id = $this->partnerOrder->id;
        $payable->user_id = $this->userId;
        $payable->user_type = $this->userType;
        $due = (double)$this->partnerOrder->dueWithLogistic;
        $payable->amount = $this->calculateAmount($due);
        $payable->emi_month = $this->resolveEmiMonth($payable);
        $payable->completion_type = $this->isAdvancedPayment ? 'advanced_order' : "order";
        $payable->success_url = $this->getSuccessUrl();
        $payable->fail_url = $this->getFailUrl();
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
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
        $discount_checking_params = (new JobDiscountCheckingParams())
            ->setDiscountableAmount($this->partnerOrder->due)
            ->setOrderAmount($this->partnerOrder->grossAmount)
            ->setPaymentGateway($this->paymentMethod);
        $job_discount_handler = app(JobDiscountHandler::class);

        $job_discount_handler->setType(DiscountTypes::ONLINE_PAYMENT)
            ->setCheckingParams($discount_checking_params)->calculate();

        if ($job_discount_handler->hasDiscount()) {
            return $job_discount_handler->getApplicableAmount();
        }
        return 0;
    }

    private function setUser()
    {
        $order = $this->partnerOrder->order;

        if ($order->partner_id) {
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
        if ($this->userType == "App\\Models\\Business") return config('sheba.business_url') . "/dashboard/orders/" . $this->partnerOrder->id;
        else return config('sheba.front_url') . '/orders/' . $this->partnerOrder->getActiveJob()->id . '/payment';
    }

    private function getFailUrl()
    {
        if ($this->userType == "App\\Models\\Business") return config('sheba.business_url');
        else return config('sheba.front_url') . '/orders/' . $this->partnerOrder->getActiveJob()->id . '/payment';
    }

    private function resolveEmiMonth(Payable $payable)
    {
        return $payable->amount >= config('sheba.min_order_amount_for_emi') ? $this->emiMonth : null;
    }

    public function setModelForPayable($model)
    {
        // TODO: Implement setModelForPayable() method.
    }

}