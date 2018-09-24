<?php

namespace Sheba\TopUp;


use App\Models\TopUpOrder;
use Sheba\ModificationFields;

class TopUp
{
    use ModificationFields;
    private $operator;
    private $vendor;
    private $agent;

    public function __construct(OperatorAgent $agent, Operator $operator)
    {
        $this->agent = $agent;
        $this->operator = $operator;
        $this->vendor = $this->operator->getVendor();
    }

    public function setOperator(Operator $operator)
    {
        $this->operator = $operator;
    }

    public function recharge($mobile_number, $amount, $type)
    {
        $response = $this->operator->recharge($mobile_number, $amount, $type);
        if (!$response) return null;
        $this->placeTopUpOrder($response, $mobile_number, $amount);
        $amount_after_commission = $amount - $this->calculateCommission($amount);
        $this->agent->topUpTransaction($amount_after_commission, $amount . " has been send to this number " . $mobile_number);
        $this->deductVendorAmount($amount);

    }

    private function calculateCommission($amount)
    {
        return (double)$amount * ($this->vendor->agent_commission / 100);
    }

    private function placeTopUpOrder($response, $mobile_number, $amount)
    {
        $topUpOrder = new TopUpOrder();
        $topUpOrder->agent_type = "App\\Models\\" . class_basename($this->agent);
        $topUpOrder->agent_id = $this->agent->id;
        $topUpOrder->payee_mobile = $mobile_number;
        $topUpOrder->amount = $amount;
        $topUpOrder->status = "Successful";
        $topUpOrder->transaction_id = $response['transaction_id'];
        $topUpOrder->transaction_details = json_encode($response['transaction_details']);
        $topUpOrder->vendor_id = $this->vendor->id;
        $topUpOrder->sheba_commission = ($amount * $this->vendor->sheba_commission) / 100;
        $topUpOrder->agent_commission = ($amount * $this->vendor->agent_commission) / 100;
        $this->setModifier($this->agent);
        $this->withCreateModificationField($topUpOrder);
        $topUpOrder->save();
    }

    private function deductVendorAmount($amount)
    {
        $this->vendor->amount -= $amount;
        $this->vendor->update();
    }

}