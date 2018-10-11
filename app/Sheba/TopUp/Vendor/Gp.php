<?php

namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\Vendor\Internal\Ssl;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

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
     * @return TopUpResponse
     * @throws \SoapFault
     */
    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
        return $this->ssl->recharge($mobile_number, $amount, $type);
    }

    /**
     * @param $amount
     * @throws \Exception
     */
    public function deductAmount($amount)
    {
        $this->model->amount -= $amount;
        $this->model->update();

        $vendor = new VendorFactory();
        $bl = $vendor->getById(5);
        $teletalk = $vendor->getById(6);
        $teletalk->deductAmount($amount);
        $bl->deductAmount($amount);

    }

}