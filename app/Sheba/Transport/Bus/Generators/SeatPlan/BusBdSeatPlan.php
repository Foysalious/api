<?php namespace Sheba\Transport\Bus\Generators\SeatPlan;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;

class BusBdSeatPlan
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
     * @param BusBdSeatPlan $bus_bd
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, BusBdSeatPlan $bus_bd)
    {
        $this->busBdClient = $bus_bd;
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

    public function getSeatPlan()
    {
        if(!$this->coachId)
            throw new \Exception('Coach id is required for seat details from bus bd.');

        $data =  $this->busBdClient->get('coaches/' . $this->coachId.  '/seats');

        if($data['data']) {
            $plan = $data['data'];
            $seatDetails = [];
            $seats = [];
            foreach ($plan['seats'] as $seat) {
                $currentSeat = [
                    "seat_id" => $seat['seatId'],
                    "seat_no" => $seat['seatNo'],
                    "seat_type_id" => $seat['seatTypeId'],
                    "status" => $seat['status'],
                    "color_code" => $seat['colorCode'],
                    "fare" => (double) $seat['fare'],
                    "x_axis" => $seat['xaxis'],
                    "y_axis" => $seat['yaxis']
                ];
                array_push($seats, $currentSeat);
            }
            $seatDetails['seats'] = $seats;
            return $seatDetails;
        } else
            throw new \Exception('Failed to parse ticket.');
    }

}