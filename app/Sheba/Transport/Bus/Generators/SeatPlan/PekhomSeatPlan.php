<?php namespace Sheba\Transport\Bus\Generators\SeatPlan;

use Sheba\Transport\Bus\ClientCalls\Pekhom;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;

class PekhomSeatPlan
{
    /** @var Pekhom $pekhomClient */
    private $pekhomClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocation_Repo;

    private $pickupAddressId = null;
    private $destinationAddressId = null;
    private $date = null;
    private $vendorId = null;
    private $coachId = null;
    /**
     * SeatPlan constructor.
     * @param BusRouteLocationRepository $bus_route_location_repo
     * @param BusBdSeatPlan $bus_bd
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, Pekhom $pekhom)
    {
        $this->pekhomClient = $pekhom;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
    }

    /**
     * @param $vendor_id
     * @return BusBdSeatPlan
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        return $this;
    }

    /**
     * @param $coach_id
     * @return BusBdSeatPlan
     */
    public function setCoachId($coach_id)
    {
        $this->coachId = $coach_id;
        return $this;
    }



}