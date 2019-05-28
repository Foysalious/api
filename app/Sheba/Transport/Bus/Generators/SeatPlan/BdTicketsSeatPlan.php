<?php namespace Sheba\Transport\Bus\Generators\SeatPlan;

use Sheba\Transport\Bus\ClientCalls\BdTickets;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;

class BdTicketsSeatPlan
{
    /** @var BdTickets $bdTicketClient */
    private $bdTicketClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocation_Repo;

    private $vendorId = null;
    private $coachId = null;

    /**
     * SeatPlan constructor.
     * @param BusRouteLocationRepository $bus_route_location_repo
     * @param BdTickets $bd_ticket_client
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, BdTickets $bd_ticket_client)
    {
        $this->bdTicketClient = $bd_ticket_client;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
    }

    /**
     * @param $vendor_id
     * @return BdTicketsSeatPlan
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        return $this;
    }

    /**
     * @param $coach_id
     * @return BdTicketsSeatPlan
     */
    public function setCoachId($coach_id)
    {
        $this->coachId = $coach_id;
        return $this;
    }

    public function getSeatPlan()
    {
        if (!$this->coachId) throw new \Exception('Coach id is required for seat details from bus bd.');

        $data = $this->bdTicketClient->get('coaches/' . $this->coachId . '/seats');

        if ($data['data']) {
            $plan = $data['data'];
            $seatDetails = [];
            $seats = [];
            foreach ($plan['seats'] as $seat) {
                $currentSeat = ["seat_id" => $seat['seatId'], "seat_no" => $seat['seatNo'], "seat_type_id" => $seat['seatTypeId'], "status" => $seat['status'],
                    "color_code" => $seat['colorCode'], "fare" => (double)$seat['fare'], "x_axis" => $seat['xaxis'], "y_axis" => $seat['yaxis'],'gender'=>$this->resolveGender($seat['color_code'])];
                array_push($seats, $currentSeat);
            }
            $seatDetails['seats'] = $seats;
            $seatDetails['maximum_selectable'] = 5;
            $seatDetails['total_seat_col'] = $plan['seatCol'];
            $seatDetails['total_seat_row'] = $plan['seatRow'];
            $boarding_points = [];
            foreach ($plan['boardingPoints'] as $boarding_point) {
                $current_boarding_point = [
                    "reporting_branch_id"=> $boarding_point['reportingBranchId'],
                    "counter_name"=> $boarding_point['counterName'],
                    "reporting_time"=> $boarding_point['reportingTime'],
                    "schedule_time"=> $boarding_point['scheduleTime']
                ];
                array_push($boarding_points, $current_boarding_point);
            }
            $dropping_points = [];
            foreach ($plan['droppingPoints'] as $dropping_point) {
                $current_dropping_point = [
                    "reporting_branch_id"=> $dropping_point['reportingBranchId'],
                    "counter_name"=> $dropping_point['counterName'],
                    "reporting_time"=> $dropping_point['reportingTime'],
                    "schedule_time"=> $dropping_point['scheduleTime']
                ];
                array_push($dropping_points, $current_dropping_point);
            }
            $seatDetails['boarding_points'] = $boarding_points;
            $seatDetails['dropping_points'] = $dropping_points;
            return $seatDetails;
        } else {
            throw new \Exception('Failed to parse ticket.');
        }
    }

    public function resolveGender($color_code)
    {
        switch ($color_code) {
            case '#ffa4a4':
            case '#FF0000':
                return 'male';

            case '#ff99ff':
            case '#ff1493':
                return 'female';
            default:
                return 'none';
        }
    }
}