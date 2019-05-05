<?php namespace Sheba\Transport\Bus\Generators;

use Sheba\Transport\Bus\ClientCalls\Busbd;
use Sheba\Transport\Bus\ClientCalls\Pekhom;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Sheba\Transport\Bus\Repositories\PekhomDestinationRouteRepository;

class VehicleList
{
    /** @var Busbd $busBdClient */
    private $busBdClient;
    /** @var Pekhom $pekhomClient */
    private $pekhomClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocation_Repo;
    private $pickupAddressId;
    private $destinationAddressId;
    private $date;

    public function __construct(BusRouteLocationRepository $bus_route_location_repo, Busbd $bus_bd, Pekhom $pekhom, PekhomDestinationRouteRepository $pekhomDestinationRouteRepo)
    {
        $this->busBdClient = $bus_bd;
        $this->pekhomClient = $pekhom;
        $this->busRouteLocation_Repo = $bus_route_location_repo;
    }

    public function setPickupAddressId($pickup_address_id)
    {
        $this->pickupAddressId = $pickup_address_id;
        return $this;
    }

    public function setDestinationAddressId($destination_address_id)
    {
        $this->destinationAddressId = $destination_address_id;
        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function getVehicles()
    {
        $pick_up_location = $this->busRouteLocation_Repo->findById($this->pickupAddressId);
        $destination_location = $this->busRouteLocation_Repo->findById($this->destinationAddressId);
        $bus_bd_vehicles =$this->parseBusBdResponse($this->busBdClient->post('coaches/search',['date' => $this->date, 'fromStationId' => $pick_up_location->bus_bd_id, 'toStationId' => $destination_location->bus_bd_id]));
        $pekhom_vehicles = $this->parsePekhomResponse($this->pekhomClient->post('bus/search.php',['journey_date' => $this->date, 'from_location_uid' => $pick_up_location->pekhom_id, 'to_location_uid' => $destination_location->pekhom_id]));
        return array_merge($bus_bd_vehicles,$pekhom_vehicles);
    }

    private function parseBusBdResponse($data)
    {
        $bus_bd_vehicles = [];
        if($data['data']) {
            $vehicles = $data['data'];
            foreach ($vehicles as  $vehicle) {
                $vehicle = (object) $vehicle;
                $current_vehicle_details = [
                    'id' => $vehicle->id,
                    'company_name' => ( (object) ($vehicle->company))->name,
                    'type' => $vehicle->coachType,
                    'start_time' => $vehicle->departureTime,
                    'start_point' => $vehicle->startCounter,
                    'end_time' => $vehicle->arrivalTime,
                    'end_point' => $vehicle->endCounter,
                    'price' => (double) $vehicle->minimumFare,
                    'seats_left' => $vehicle->availableSeats,
                    'code' => $vehicle->coachNo,
                    'vendor_id' => 1
                ];
                array_push($bus_bd_vehicles, $current_vehicle_details);
            }
        }
        return $bus_bd_vehicles;
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
                    'company_name' => ($vehicle->bus_name),
                    'type' => $vehicle->bus_type,
                    'start_time' => $vehicle->departure_time,
                    'start_point' => $vehicle->start_counter,
                    'end_time' => $vehicle->arrival_time,
                    'end_point' => $vehicle->end_counter,
                    'price' => (double) $vehicle->price[$vehicle->seat_class[0]],
                    'seats_left' => $vehicle->available_seat,
                    'code' => $vehicle->bus_no,
                    'vendor_id' => 2,
                ];
                array_push($pekhom_vehicles, $current_vehicle_details);
            }
        }
        return $pekhom_vehicles;
    }
}