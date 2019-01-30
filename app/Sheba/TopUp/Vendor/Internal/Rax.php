<?php

namespace Sheba\TopUp\Vendor\Internal;

use Sheba\TopUp\TopUpRequest;
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

    public function recharge(TopUpRequest $top_up_request): TopUpResponse
    {
        $this->setup();
        return $this->rax->setPin($this->pin)->setMId($this->mid)->recharge($top_up_request);
    }

    public function getTopUpInitialStatus()
    {
        return config('topup.status.successful');
    }
}