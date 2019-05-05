<?php namespace Sheba\Transport\Bus\Generators\SeatPlan;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;

class BusBd
{
    /** @var \Sheba\Transport\Bus\ClientCalls\Busbd $busBdClient */
    private $busBdClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocation_Repo;

    private $vendorId = null;
    private $coachId = null;
    /**
     * SeatPlan constructor.
     * @param BusRouteLocationRepository $bus_route_location_repo
     * @param Busbd $bus_bd
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, Busbd $bus_bd)
    {
        $this->busBdClient = $bus_bd;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
    }

    /**
     * @param $vendor_id
     * @return BusBd
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        return $this;
    }

    /**
     * @param $coach_id
     * @return BusBd
     */
    public function setCoachId($coach_id)
    {
        $this->coachId = $coach_id;
        return $this;
    }



}