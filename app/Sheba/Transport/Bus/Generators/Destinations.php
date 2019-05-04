<?php namespace Sheba\Transport\Bus\Generators;

use Sheba\Transport\Bus\ClientCalls\Busbd;
use Sheba\Transport\Bus\ClientCalls\Pekhom;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;

class Destinations
{
    /** @var Busbd $busBdClient */
    private $busBdClient;
    /** @var Pekhom $pekhomClient */
    private $pekhomClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocation_Repo;
    private $pickupAddressId;

    public function __construct(BusRouteLocationRepository $bus_route_location_repo, Busbd $bus_bd, Pekhom $pekhom)
    {
        $this->busBdClient = $bus_bd;
        $this->pekhomClient = $pekhom;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
    }

    public function setPickupAddressId($pickup_address_id)
    {
        $this->pickupAddressId = $pickup_address_id;
        return $this;
    }

    public function getDestinations()
    {
        $bus_route_location = $this->busRouteLocation_Repo->findById($this->pickupAddressId);
        return $this->busBdClient->get('routes/to/' . $bus_route_location->bus_bd_id)["data"];
    }
}