<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use App\Models\Procurement;
use Carbon\Carbon;

class ProcurementAdapter implements PayableAdapter
{
    private $emiMonth;
    /** @var Procurement */
    private $procurement;


    public function setModelForPayable($model)
    {
        $this->procurement = $model;
        return $this;
    }

    public function setEmiMonth($month)
    {
        $this->emiMonth = (int)$month;
        return $this;
    }

    public function getPayable(): Payable
    {
        $bid = $this->procurement->getActiveBid();
        $payable = new Payable();
        $payable->type = 'procurement';
        $payable->type_id = $this->procurement->is;
        $payable->user_id = $this->procurement->owner_id;
        $payable->user_type = $this->procurement->ownerType;
        $payable->amount = (double)$bid->price;
        $payable->emi_month = $this->resolveEmiMonth($payable);
        $payable->completion_type = "procurement";
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
        return config('sheba.business_url');
    }

    private function getFailUrl()
    {
        return config('sheba.business_url');
    }
}