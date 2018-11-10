<?php

namespace Sheba\TopUp\Vendor;

use App\Models\TopUpRechargeHistory;
use App\Models\TopUpVendor;
use Sheba\TopUp\Vendor\Internal\Ssl;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use DB;
use Carbon\Carbon;

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
        $this->createNewRechargeHistory($amount, 4);
        $this->createNewRechargeHistory($amount, 5);
        $this->createNewRechargeHistory($amount, 6);
    }

    private function createNewRechargeHistory($amount, $vendor_id)
    {
        $recharge_history = new TopUpRechargeHistory();
        $recharge_history->recharge_date = Carbon::now();
        $recharge_history->vendor_id = $vendor_id;
        $recharge_history->amount = $amount;
        $recharge_history->save();
    }
}