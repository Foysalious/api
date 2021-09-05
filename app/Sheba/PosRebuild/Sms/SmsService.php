<?php namespace App\Sheba\PosRebuild\Sms;

class SmsService
{

    private $type;
    private $typeId;

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
        $type = $this->type;
        $class = $type.'::class';
        (new ($class))->send();
    }



}