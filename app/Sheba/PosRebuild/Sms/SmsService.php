<?php namespace App\Sheba\PosRebuild\Sms;

use App\Sheba\PosRebuild\Sms\Types\OrderBill;
use App\Sheba\PosRebuild\Sms\Types\WebStoreOrder;

class SmsService
{
    private $type;
    private $typeId;
    private $partner;

    public function setPartner($partner)
    {
        $this->partner = $partner;
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
        $class = $this->generateClass();
        return $class->send($this->partner, $this->typeId);
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