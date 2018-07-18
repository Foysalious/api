<?php

namespace Sheba\TopUp;


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

    public function recharge($msisdn)
    {
        $this->operator->recharge($msisdn);
    }

}