<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\Payable;
use Carbon\Carbon;
use Sheba\Logistics\Repository\ParcelRepository;

class OrderAdapter implements PayableAdapter
{
    /** @var PartnerOrder $partnerOrder */
    private $partnerOrder;
    private $isAdvancedPayment;
    private $userId;
    private $userType;
    /** @var Job $job */
    private $job;

    public function __construct(PartnerOrder $partner_order, $is_advanced_payment = false)
    {
        $this->partnerOrder = $partner_order;
        $this->partnerOrder->calculate(true);
        $this->isAdvancedPayment = $is_advanced_payment;
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
        $due = (double)$this->partnerOrder->due + $this->getShebaLogisticsPrice();
        $payable->amount = $this->calculateAmount($due);
        $payable->completion_type = $this->isAdvancedPayment ? 'advanced_order' : "order";
        $payable->success_url = $this->getSuccessUrl();
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
        if ($this->userType == "App\\Models\\Business") return config('sheba.business_url') . "/dashboard/orders/" . $this->id;
        else return config('sheba.front_url') . '/orders/' . $this->job->id;
    }

    public function setModelForPayable($model)
    {
        // TODO: Implement setModelForPayable() method.
    }
}