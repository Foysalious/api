<?php namespace Sheba\Transport\Bus\Generators;

use Sheba\Transport\Bus\ClientCalls\BdTickets;
use Sheba\Transport\Bus\ClientCalls\Pekhom;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Sheba\Transport\Bus\Repositories\PekhomDestinationRouteRepository;

class Destinations
{
    /** @var BdTickets $bdTicketClient */
    private $bdTicketClient;
    /** @var Pekhom $pekhomClient */
    private $pekhomClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocation_Repo;
    /** @var PekhomDestinationRouteRepository $pekhomDestinationRouteRepo */
    private $pekhomDestinationRouteRepo;
    private $pickupAddressId;

    public function __construct(BusRouteLocationRepository $bus_route_location_repo, BdTickets $bd_tickets, Pekhom $pekhom, PekhomDestinationRouteRepository $pekhomDestinationRouteRepo)
    {
        $this->bdTicketClient = $bd_tickets;
        $this->pekhomClient = $pekhom;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
        $this->pekhomDestinationRouteRepo = $pekhomDestinationRouteRepo;
    }

    public function setPickupAddressId($pickup_address_id)
    {
        $this->pickupAddressId = $pickup_address_id;
        return $this;
    }

    public function getDestinations()
    {
        $bus_route_location = $this->busRouteLocation_Repo->findById($this->pickupAddressId);
        $bus_bd_destinations = $pekhom_destinations = [];
        if ($bus_route_location->bus_bd_id) $bus_bd_destinations = collect($this->bdTicketClient->get('routes/to/' . $bus_route_location->bus_bd_id)["data"])->pluck('id')->toArray();
        if ($bus_route_location->pekhom_id) $pekhom_destinations = collect($this->pekhomDestinationRouteRepo->findIdsByColumnName('location_from_uid', [$bus_route_location->pekhom_id]))->pluck('location_uid')->toArray();
        $bus_bd_locations = $this->busRouteLocation_Repo->findIdsByColumnName('bus_bd_id', $bus_bd_destinations);
        $pekhom_locations = $this->busRouteLocation_Repo->findIdsByColumnName('pekhom_id', $pekhom_destinations);
        $merged_destinations = $bus_bd_locations->merge($pekhom_locations)->unique()->values();
        return $merged_destinations;
    }

}