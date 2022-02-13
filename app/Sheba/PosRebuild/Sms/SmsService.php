<?php namespace App\Sheba\PosRebuild\Sms;

use App\Models\Partner;
use App\Sheba\PosRebuild\Sms\Types\OrderBill;
use App\Sheba\PosRebuild\Sms\Types\WebStoreOrder;

class SmsService
{
    private $type;
    private $typeId;
    private $partnerId;

    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
        return $this;
    }

    public function sendSMS()
    {
        $partner = Partner::find($this->partnerId);
        $class = $this->generateClass();
        return $class->send($partner, $this->typeId);
    }

    /**
     * @return SmsSendInterface
     */
    private function generateClass()
    {
        if ($this->type == Types::WEB_STORE_ORDER_SMS)
            return app(WebStoreOrder::class);
        elseif ($this->type == Types::ORDER_BILL_SMS)
            return app(OrderBill::class);
    }


}