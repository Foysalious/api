<?php namespace App\Sheba;

use App\Sheba\Partner\Delivery\Methods;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInformation;

class PartnerGeneralSettings
{

    private $partner;

    /**
     * @param mixed $partner
     * @return PartnerGeneralSettings
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }


    public function getGeneralSettings()
    {
        $data['delivery_settings'] = $this->getDeliverySettings();
        return $data;
    }

    private function getDeliverySettings()
    {
        $partnerDeliveryInformation = PartnerDeliveryInformation::where('partner_id', $this->partner->id)->first();
        $delivery_method = !empty($partnerDeliveryInformation) ? $partnerDeliveryInformation->delivery_vendor : Methods::OWN_DELIVERY;
        return [
            'delivery_method' => $delivery_method,
            'delivery_price' => $delivery_method == Methods::OWN_DELIVERY ? $this->partner->delivery_charge : null
        ];
    }


}