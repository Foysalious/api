<?php namespace Sheba\Transport\Bus\Generators;

use Sheba\Transport\Bus\ClientCalls\Busbd;
use Sheba\Transport\Bus\ClientCalls\Pekhom;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Sheba\Transport\Bus\Repositories\PekhomDestinationRouteRepository;

class Routes
{
    /** @var Busbd $busBdClient */
    private $busBdClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocationRepo;
    /** @var PekhomDestinationRouteRepository $pekhomDestinationRouteRepo */
    private $pekhomDestinationRouteRepo;
    /** @var Pekhom $pekhomClient */
    private $pekhomClient;

    /**
     * Routes constructor.
     * @param BusRouteLocationRepository $bus_route_location_repo
     * @param Busbd $bus_bd
     * @param Pekhom $pekhom
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, Busbd $bus_bd, Pekhom $pekhom,PekhomDestinationRouteRepository $pekhomDestinationRouteRepo)
    {
        $this->busBdClient = $bus_bd;
        $this->busRouteLocationRepo = $bus_route_location_repo;
        $this->pekhomClient = $pekhom;
        $this->pekhomDestinationRouteRepo = $pekhomDestinationRouteRepo;
    }

    public function generate()
    {
        $bus_bd_route = $this->busBdClient->get('routes/from');

        if (config('bus_transport.pekhom.is_active')) {
            $pekhom_route = $this->pekhomClient->post('bus/routes.php', null);
            $pekhom_to_routes = $pekhom_route['api_data']['to_route'];
            $pekhom_route = collect($pekhom_route['api_data']['from_route']);
            $this->pekhomDestinationRouteRepo->insert($pekhom_to_routes);
        }
        $locations = [];
        foreach ($bus_bd_route['data'] as $key => $location) {
            $pekhom_location_name = (config('bus_transport.pekhom.is_active')) ? $pekhom_route->where('location_name', $location['name'])->first() : null;
            $locations[] = [
                'name'      => $location['name'],
                'bus_bd_id' => $location['id'],
                'pekhom_id' => $pekhom_location_name ? $pekhom_location_name['location_uid'] : null
            ];
        }

        return $this->busRouteLocationRepo->insert($locations);
    }
}