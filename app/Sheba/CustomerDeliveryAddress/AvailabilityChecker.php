<?php namespace Sheba\CustomerDeliveryAddress;


use App\Models\CustomerDeliveryAddress;
use App\Models\Partner;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;

class AvailabilityChecker
{
    private $deliveryAddress;
    private $partner;

    public function setAddress(CustomerDeliveryAddress $address)
    {
        $this->deliveryAddress = $address;
        return $this;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function isAvailable()
    {
        $partner_geo = json_decode($this->partner->geo_informations);
        $to = [new Coords(floatval($partner_geo->lat), floatval($partner_geo->lng), $this->partner->id)];
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $address_geo = $this->deliveryAddress->geo;
        $current = new Coords($address_geo->lat, $address_geo->lng);
        return $distance->from([$current])->to($to)->sortedDistance()[0][$to[0]->id] <= (double)$partner_geo->radius * 1000;
    }


}