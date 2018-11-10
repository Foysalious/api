<?php

namespace Sheba\TopUp\Vendor\Internal;

use Sheba\TopUp\Vendor\Response\TopUpResponse;

trait Rax
{
    private $rax;

    private $pin;
    private $mid;

    public function __construct(RaxClient $rax)
    {
        $this->rax = $rax;
    }

    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
        $this->setup();
        return $this->rax->setPin($this->pin)->setMId($this->mid)->recharge($mobile_number, $amount, $type);
    }
}