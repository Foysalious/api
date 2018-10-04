<?php

namespace Sheba\TopUp;


use App\Models\TopUpOrder;
use Illuminate\Database\QueryException;
use Sheba\ModificationFields;
use DB;
use Sheba\TopUp\Vendor\Vendor;

class TopUp
{
    use ModificationFields;
    /** @var Vendor */
    private $vendor;
    /** @var \App\Models\TopUpVendor */
    private $model;
    /** @var TopUpAgent */
    private $agent;

    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function setVendor(Vendor $model)
    {
        $this->vendor = $model;
        $this->model = $this->vendor->getModel();
        return $this;
    }

    public function recharge($mobile_number, $amount, $type)
    {
        //$mobile_number = formatMobile($mobile_number);
        $response = $this->vendor->recharge($mobile_number, $amount, $type);
        DB::transaction(function () use ($response, $mobile_number, $amount) {
            $this->placeTopUpOrder($response, $mobile_number, $amount);
            $amount_after_commission = $amount - $this->calculateCommission($amount);
            $this->agent->topUpTransaction($amount_after_commission, $amount . " has been topped up to " . $mobile_number);
            $this->deductVendorAmount($amount);
        });
    }

    private function calculateCommission($amount)
    {
        return (double)$amount * ($this->model->agent_commission / 100);
    }

    private function placeTopUpOrder($response, $mobile_number, $amount)
    {
        $topUpOrder = new TopUpOrder();
        $topUpOrder->agent_type = "App\\Models\\" . class_basename($this->agent);
        $topUpOrder->agent_id = $this->agent->id;
        $topUpOrder->payee_mobile = $mobile_number;
        $topUpOrder->amount = $amount;
        $topUpOrder->status = "Successful";
        $topUpOrder->transaction_id = $response->transactionId;
        $topUpOrder->transaction_details = json_encode($response->transactionDetails);
        $topUpOrder->vendor_id = $this->model->id;
        $topUpOrder->sheba_commission = ($amount * $this->model->sheba_commission) / 100;
        $topUpOrder->agent_commission = ($amount * $this->model->agent_commission) / 100;
        $this->setModifier($this->agent);
        $this->withCreateModificationField($topUpOrder);
        $topUpOrder->save();
    }

    private function deductVendorAmount($amount)
    {
        $this->model->amount -= $amount;
        $this->model->update();
    }

}