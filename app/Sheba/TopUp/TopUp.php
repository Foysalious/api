<?php

namespace Sheba\TopUp;


use App\Models\TopUpOrder;

class TopUp
{
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

    public function recharge($mobile_number)
    {
        $this->operator->recharge($mobile_number);
    }

    private function placeTopUpOrder()
    {
        $topUpOrder = new TopUpOrder();
        $topUpOrder->agent_type = "App\\Models\\" . class_basename($this->agent);
        $topUpOrder->agent_id = $this->agent->id;
        $topUpOrder->payee_mobile = $this->agent->id;
    }

}