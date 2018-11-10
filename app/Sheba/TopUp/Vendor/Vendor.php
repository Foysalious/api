<?php namespace Sheba\TopUp\Vendor;

use App\Models\TopUpRechargeHistory;
use App\Models\TopUpVendor;
use Carbon\Carbon;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

abstract class Vendor
{
    protected $model;

    public function setModel(TopUpVendor $model)
    {
        $this->model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function isPublished()
    {
        return $this->model->is_published;
    }

    abstract function recharge($mobile_number, $amount, $type): TopUpResponse;

    public function deductAmount($amount)
    {
        $this->model->amount -= $amount;
        $this->model->update();
    }

    public function refill($amount)
    {
        $this->model->amount += $amount;
        $this->model->update();
        $this->createNewRechargeHistory($amount);
    }

    private function createNewRechargeHistory($amount)
    {
        $recharge_history = new TopUpRechargeHistory();
        $recharge_history->recharge_date = Carbon::now();
        $recharge_history->vendor_id = $this->model->id;
        $recharge_history->amount = $amount;
        $recharge_history->save();
    }

}