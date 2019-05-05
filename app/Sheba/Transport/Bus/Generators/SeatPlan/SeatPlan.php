<?php namespace Sheba\Transport\Bus\Generators\SeatPlan;

use Sheba\Transport\Bus\ClientCalls\Busbd;
use Sheba\Transport\Bus\ClientCalls\Pekhom;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Sheba\Transport\Bus\Repositories\PekhomDestinationRouteRepository;

abstract class SeatPlan
{
    /** @var Busbd $busBdClient */
    private $busBdClient;
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
     * @param Busbd $bus_bd
     * @param Pekhom $pekhom
     * @param PekhomDestinationRouteRepository $pekhomDestinationRouteRepo
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, Busbd $bus_bd, Pekhom $pekhom, PekhomDestinationRouteRepository $pekhomDestinationRouteRepo)
    {
        $this->busBdClient = $bus_bd;
        $this->pekhomClient = $pekhom;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
    }

    /**
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
     * @return SeatPlan
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param $vendor_id
     * @return SeatPlan
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        return $this;
    }

    /**
     * @param $coach_id
     * @return SeatPlan
     */
    public function setCoachId($coach_id)
    {
        $this->coachId = $coach_id;
        return $this;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getSeatPlan()
    {
        $pick_up_location = $this->busRouteLocation_Repo->findById($this->pickupAddressId);
        $destination_location = $this->busRouteLocation_Repo->findById($this->destinationAddressId);
        $seatPlan = $this->resolveSeatPlan();
        return $seatPlan;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function resolveSeatPlan()
    {
        switch ($this->vendorId) {
            case 1:
                // Bus Bd
                return $this->getSeatDetailsFromBusBd();
            case 2:
                // Pekhom

                break;
            default:
                throw new \Exception('Invalid Vendor');
                break;
        }
    }

    private function getSeatDetailsFromBusBd()
    {
        if(!$this->coachId)
            throw new \Exception('Coach id is required for seat details from bus bd.');

        $data =  $this->busBdClient->get('coaches/' . $this->coachId.  '/seats');

        if($data['data']) {
            $plan = $data['data'];
            dd($plan);
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

    private function parsePekhomResponse($data)
    {
        $pekhom_vehicles = [];
        if(isset($data['api_reponse']) && $data['api_reponse']['status'] === 'OK' ) {
            $vehicles = $data['api_data'];
            foreach ($vehicles as  $vehicle) {
                $vehicle = (object) $vehicle;
                $current_vehicle_details = [
                    'id' => $vehicle->bus_id,
                    'company_name' => ($vehicle->bus_name   ),
                    'type' => $vehicle->bus_type,
                    'start_time' => $vehicle->departure_time,
                    'start_point' => $vehicle->start_counter,
                    'end_time' => $vehicle->arrival_time,
                    'end_point' => $vehicle->end_counter,
                    'price' => $vehicle->price[$vehicle->seat_class[0]],
                    'seats_left' => $vehicle->available_seat,
                    'code' => $vehicle->bus_no
                ];
                array_push($pekhom_vehicles, $current_vehicle_details);
            }
        }
        return $pekhom_vehicles;
    }
}