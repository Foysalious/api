<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use App\Models\Procurement;
use Carbon\Carbon;

class ProcurementAdapter implements PayableAdapter
{
    private $emiMonth;
    /** @var Procurement */
    private $procurement;
    /** @var mixed $bid */
    private $bid;

    public function setModelForPayable($model)
    {
        $this->procurement = $model;
        $this->bid = $this->procurement->getActiveBid();
        return $this;
    }

    public function setEmiMonth($month)
    {
        $this->emiMonth = (int)$month;
        return $this;
    }

    public function getPayable(): Payable
    {
        $this->procurement->calculate();
        $payable = new Payable();
        $payable->type = 'procurement';
        $payable->type_id = $this->procurement->id;
        $payable->user_id = $this->procurement->owner_id;
        $payable->user_type = $this->procurement->owner_type;
        $payable->amount = (double)$this->procurement->due;
        $payable->emi_month = $this->resolveEmiMonth($payable);
        $payable->completion_type = 'procurement';
        $payable->success_url = $this->getSuccessUrl();
        $payable->fail_url = $this->getFailUrl();
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
    }

    private function resolveEmiMonth(Payable $payable)
    {
        return $payable->amount >= config('sheba.min_order_amount_for_emi') ? $this->emiMonth : null;
    }

    private function getSuccessUrl()
    {
        return config('sheba.business_url') . '/dashboard/orders/rfq/' . $this->procurement->id . '/bill?bidId=' . $this->bid->id;
    }

    private function getFailUrl()
    {
        return config('sheba.business_url') . '/dashboard/orders/rfq/' . $this->procurement->id . '/bill?bidId=' . $this->bid->id;
    }

    public function canInit(): bool
    {
        return true;
    }
}
