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
     * @param Pekhom $pekhom
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, Pekhom $pekhom)
    {
        $this->pekhomClient = $pekhom;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
    }

    /*
    * @param $pickup_address_id
    * @return $this
    */
    public function setPickupAddressId($pickup_address_id)
    {
        $this->pickupAddressId = $pickup_address_id;
        return $this;
    }

    /**
     * @param $destination_address_id
     * @return $this
     */
    public function setDestinationAddressId($destination_address_id)
    {
        $this->destinationAddressId = $destination_address_id;
        return $this;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param $vendor_id
     * @return $this
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        return $this;
    }

    /**
     * @param $coach_id
     * @return $this
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

        $pick_up_location = $this->busRouteLocation_Repo->findById($this->pickupAddressId);
        $destination_location = $this->busRouteLocation_Repo->findById($this->destinationAddressId);

        $data =  $this->pekhomClient->post('bus/seat_plan.php',['journey_date' => $this->date,
            'from_location_uid' => $pick_up_location->pekhom_id,
            'to_location_uid' => $destination_location->pekhom_id, 'bus_id' => $this->coachId]);

        if(isset($data['api_reponse']) && $data['api_reponse']['status'] === 'OK' ) {
            $plan = $data['api_data'];
            $seatDetails = [];
            $seats = [];
            foreach ($plan['seatstructure_details'] as $seat) {
                if(is_array($seat))
                {
                    $current_seat = [
                        "seat_id" => $seat['seatid'],
                        "seat_no" => $seat['seat_no'],
                        "seat_type_id" => $seat['seat_type_id'],
                        "status" => $seat['status'],
                        "color_code" => $seat['colourcode'],
                        "fare" => (double) $seat['fare'],
                        "x_axis" => $seat['x_axis'],
                        "y_axis" => $seat['y_axis']
                    ];
                    array_push($seats, $current_seat);
                }
            }
            $seatDetails['seats'] = $seats;
            $seatDetails['maximum_selectable'] = 5;
            $seatDetails['total_seat_col'] = $plan['column_no'];
            $seatDetails['total_seat_row'] = $plan['row_no'];
            $boarding_points = [];
            if(is_array($plan['bording_points'])) {
                foreach ($plan['bording_points'] as $boarding_point) {
                    $current_boarding_point = [
                        "reporting_branch_id"=> $boarding_point['reporting_branch_id'],
                        "counter_name"=> $boarding_point['counter_name'],
                        "reporting_time"=> $boarding_point['reporting_time'],
                        "schedule_time"=> $boarding_point['schedule_time']
                    ];
                    array_push($boarding_points, $current_boarding_point);
                }
            }

            $dropping_points = [];
            if(is_array($plan['dropping_points'])) {
                foreach ($plan['dropping_points'] as $dropping_point) {
                    $current_dropping_point = [
                        "reporting_branch_id"=> $dropping_point['reporting_branch_id'],
                        "counter_name"=> $dropping_point['counter_name'],
                        "reporting_time"=> $dropping_point['droping_time'],
                        "schedule_time"=> $dropping_point['schedule_time']
                    ];
                    array_push($dropping_points, $current_dropping_point);
                }
            }
            $seatDetails['boarding_points'] = $boarding_points;
            $seatDetails['dropping_points'] = $dropping_points;
            return $seatDetails;
        } else
            throw new \Exception('Failed to parse ticket.');
    }

}