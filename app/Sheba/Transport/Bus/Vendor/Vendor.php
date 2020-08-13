<?php namespace Sheba\Transport\Bus\Vendor;

use App\Models\Transport\TransportTicketVendor;
use Sheba\Transport\Bus\Order\TransportTicketRequest;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;

abstract class Vendor
{
    protected $model;
    /** @var BusRouteLocationRepository $busRouteLocationRepo */
    protected $busRouteLocationRepo;

    public function __construct(BusRouteLocationRepository $bus_route_location_repo)
    {
        $this->busRouteLocationRepo = $bus_route_location_repo;
    }

    public function setModel(TransportTicketVendor $model)
    {
        $this->model = $model;
        return $this;
    }

    abstract function bookTicket(TransportTicketRequest $ticket_request);

    abstract function confirmTicket($ticket_id);

    abstract function balance();
}
