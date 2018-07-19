<?php

namespace Sheba\TopUp;


use App\Models\TopUpOrder;
use Sheba\ModificationFields;

class TopUp
{
    use ModificationFields;
    private $operator;
    private $agent;

    public function __construct(OperatorAgent $agent, Operator $operator)
    {
        $this->agent = $agent;
        $this->operator = $operator;
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
    }

    private function placeTopUpOrder($mobile_number, $amount)
    {
        $topUpOrder = new TopUpOrder();
        $topUpOrder->agent_type = "App\\Models\\" . class_basename($this->agent);
        $topUpOrder->agent_id = $this->agent->id;
        $topUpOrder->payee_mobile = $mobile_number;
        $topUpOrder->amount = $amount;
        $topUpOrder->status = "Successful";
        $vendor = $this->operator->getVendor();
        $topUpOrder->vendor_id = $vendor->id;
        $topUpOrder->sheba_commission = ($amount * $vendor->sheba_commission) / 100;
        $topUpOrder->agent_commission = ($amount * $vendor->agent_commission) / 100;
        $this->setModifier($this->agent);
        $this->withBothModificationFields($topUpOrder);
        $topUpOrder->save();
    }

}