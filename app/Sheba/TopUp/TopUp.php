<?php

namespace Sheba\TopUp;


class TopUp
{
    public $operator;

    public function __construct(Operator $operator)
    {
        $this->operator = $operator;
    }

    public function rechargeMobile()
    {

    }

}