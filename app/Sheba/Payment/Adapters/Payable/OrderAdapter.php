<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\PartnerOrder;
use App\Models\Payable;
use Carbon\Carbon;

class OrderAdapter implements PayableAdapter
{
    private $partnerOrder;
    private $isAdvancedPayment;
    private $userId;
    private $userType;

    public function __construct(PartnerOrder $partner_order, $is_advanced_payment = false)
    {
        $this->partnerOrder = $partner_order;
        $this->partnerOrder->calculate(true);
        $this->isAdvancedPayment = $is_advanced_payment;
        $this->setUser();
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'partner_order';
        $payable->type_id = $this->partnerOrder->id;
        $payable->user_id = $this->userId;
        $payable->user_type = $this->userType;
        $payable->amount = (double)$this->partnerOrder->due;
        $payable->completion_type = $this->isAdvancedPayment ? 'advanced_order' : "order";
        $payable->success_url = config('sheba.front_url') . '/orders/' . $this->partnerOrder->jobs()->where('status', '<>', constants('JOB_STATUSES')['Cancelled'])->first()->id;
        $payable->created_at = Carbon::now();
        $payable->save();

        return $payable;
    }

    private function setUser()
    {
        $order = $this->partnerOrder->order;

        if ($order->partner_id) {
            $this->userId   = $order->partner_id;
            $this->userType = "App\\Models\\Partner";
        } else {
            $this->userId   = $order->customer_id;
            $this->userType = "App\\Models\\Customer";
        }
    }
}