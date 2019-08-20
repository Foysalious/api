<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\Payable;
use Carbon\Carbon;

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

    public function __construct(PartnerOrder $partner_order, $is_advanced_payment = false)
    {
        $this->partnerOrder = $partner_order;
        $this->partnerOrder->calculate(true);
        $this->isAdvancedPayment = $is_advanced_payment;
        $this->emiMonth = null;
        $this->setUser();
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
        $payable->emi_month = $this->emiMonth;
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
            $due -= ($due * (config('sheba.online_payment_discount_percentage') / 100));
        }
        return $due;
    }

    private function getShebaLogisticsPrice()
    {
        if ($this->job->needsLogistic())
            return $this->job->category->getShebaLogisticsPrice();

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
        else return config('sheba.front_url') . '/orders/' . $this->partnerOrder->getActiveJob()->id;
    }

    private function getFailUrl()
    {
        if ($this->userType == "App\\Models\\Business") return config('sheba.business_url');
        else return config('sheba.front_url');
    }


    public function setModelForPayable($model)
    {
        // TODO: Implement setModelForPayable() method.
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
}