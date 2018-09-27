<?php

namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\TopUpResponse;
use Sheba\TopUp\Vendor\Internal\Ssl;

class Teletalk extends Vendor
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
     * @return TopUpResponse
     * @throws \SoapFault
     */
    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
        return $this->ssl->recharge($mobile_number, $amount, $type);
    }
}