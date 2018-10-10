<?php

namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\TopUpSuccessResponse;
use Sheba\TopUp\Vendor\Internal\Ssl;

class Gp extends Vendor
{
    private $ssl;

    public function __construct(Ssl $ssl)
    {
        $this->ssl = $ssl;
    }

    /**
     * @param $mobile_number
     * @param $amount
     * @param $type
     * @return TopUpSuccessResponse
     * @throws \SoapFault
     */
    public function recharge($mobile_number, $amount, $type): TopUpSuccessResponse
    {
        return $this->ssl->recharge($mobile_number, $amount, $type);
    }
}