<?php namespace App\Sheba\PosRebuild\Sms\Types;

use App\Sheba\PosRebuild\Sms\SmsSendInterface;
use Sheba\Pos\Jobs\OrderBillSms;

class OrderBill implements SmsSendInterface
{

    public function send($partner, $typeId)
    {
        return dispatch(new OrderBillSms($partner, $typeId));
    }
}