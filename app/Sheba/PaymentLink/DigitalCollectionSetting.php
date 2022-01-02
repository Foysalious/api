<?php

namespace Sheba\PaymentLink;

use Sheba\Dal\DigitalCollectionSetting\Contract as DigitalCollectionRepository;

class DigitalCollectionSetting
{
    private $partner;
    private $digitalCollectionSetting;

    public function __construct()
    {
        $this->digitalCollectionSetting = app()->make(DigitalCollectionRepository::class);
    }

    /**
     * @param mixed $partner
     * @return DigitalCollectionSetting
     */
    public function setPartner($partner): DigitalCollectionSetting
    {
        $this->partner = $partner;
        return $this;
    }

    public function getServiceCharge(): float
    {
        if(isset($this->partner))
            $digitalCollection = $this->digitalCollectionSetting->where("partner_id", $this->partner->id)->first();
        return isset($digitalCollection) ? $digitalCollection->service_charge : PaymentLinkStatics::SERVICE_CHARGE;
    }
}