<?php namespace Sheba\Transport\Bus\Generators;

use Sheba\Transport\Bus\ClientCalls\Busbd;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;

class Routes
{
    /** @var Busbd $busBdClient */
    private $busBdClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocation_Repo;

    /**
     * Routes constructor.
     * @param BusRouteLocationRepository $bus_route_location_repo
     * @param Busbd $bus_bd
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, Busbd $bus_bd)
    {
        $this->busBdClient = $bus_bd;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
    }

    public function generate()
    {
        $route = $this->busBdClient->get('routes/from');
        $locations = [];
        foreach ($route['data'] as $key => $location) {
            $locations[] = [
                'name'      => $location['name'],
                'bus_bd_id' => $location['id'],
                'pekhom_id' => 'pekhom_' . ++$key
            ];
        }

        return $this->busRouteLocation_Repo->insert($locations);
    }
}