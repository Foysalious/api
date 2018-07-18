<?php

namespace Sheba\TopUp;


class TopUp
{
    private $operator;

    public function __construct(Operator $operator)
    {
        $this->operator = $operator;
    }

    public function setOperator(Operator $operator)
    {
        $this->operator = $operator;
    }

    public function recharge($to, $from)
    {
        $this->operator->recharge($to, $from);
    }

}