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
        $this->operator->recharge($mobile_number, $amount, $type);
        $this->placeTopUpOrder($mobile_number, $amount);
        $this->agent->topUpTransaction($amount, $amount . " has been send to this number " . $mobile_number);
        $this->deductVendorAmount($amount);

    }

    private function placeTopUpOrder($mobile_number, $amount)
    {
        $topUpOrder = new TopUpOrder();
        $topUpOrder->agent_type = "App\\Models\\" . class_basename($this->agent);
        $topUpOrder->agent_id = $this->agent->id;
        $topUpOrder->payee_mobile = $mobile_number;
        $topUpOrder->amount = $amount;
        $topUpOrder->status = "Successful";
        $topUpOrder->vendor_id = $this->vendor->id;
        $topUpOrder->sheba_commission = ($amount * $this->vendor->sheba_commission) / 100;
        $topUpOrder->agent_commission = ($amount * $this->vendor->agent_commission) / 100;
        $this->setModifier($this->agent);
        $this->withBothModificationFields($topUpOrder);
        $topUpOrder->save();
    }

    private function deductVendorAmount($amount)
    {
        $this->vendor->amount -= $amount;
        $this->vendor->update();
    }

}