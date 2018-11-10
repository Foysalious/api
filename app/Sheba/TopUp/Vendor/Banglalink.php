<?php

namespace Sheba\TopUp\Vendor;


use App\Models\TopUpVendor;
use Sheba\TopUp\Vendor\Internal\Ssl;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Banglalink extends Vendor
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
        TopUpVendor::whereIn('id', [4, 5, 6])->update(['amount' => $this->model->amount - $amount]);
    }

    public function refill($amount)
    {
        TopUpVendor::whereIn('id', [4, 5, 6])->update(['amount' => $this->model->amount + $amount]);
    }
}