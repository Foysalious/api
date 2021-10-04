<?php namespace App\Sheba\PosRebuild\Sms\Types;

use App\Sheba\PosRebuild\Sms\SmsSendInterface;
use Sheba\Pos\Jobs\WebstoreOrderSms;

class WebStoreOrder implements SmsSendInterface
{
    public function send($partner, $typeId)
    {
        return dispatch(new WebstoreOrderSms($partner, $typeId));
    }
}