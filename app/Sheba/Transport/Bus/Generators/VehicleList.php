<?php namespace Sheba\Transport\Bus\Generators;

use Carbon\Carbon;
use Sheba\Transport\Bus\ClientCalls\BdTickets;
use Sheba\Transport\Bus\ClientCalls\Pekhom;
use Sheba\Transport\Bus\Exception\InvalidLocationAddressException;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Sheba\Transport\Bus\Repositories\PekhomDestinationRouteRepository;

class VehicleList
{
    /** @var BdTickets $bdTicketClient */
    private $bdTicketClient;
    /** @var Pekhom $pekhomClient */
    private $pekhomClient;
    /** @var BusRouteLocationRepository $busRouteLocation_Repo */
    private $busRouteLocation_Repo;
    private $pickupAddressId;
    private $destinationAddressId;
    private $date;

    /**
     * VehicleList constructor.
     * @param BusRouteLocationRepository $bus_route_location_repo
     * @param BdTickets $bd_ticket
     * @param Pekhom $pekhom
     * @param PekhomDestinationRouteRepository $pekhomDestinationRouteRepo
     */
    public function __construct(BusRouteLocationRepository $bus_route_location_repo, BdTickets $bd_ticket, Pekhom $pekhom, PekhomDestinationRouteRepository $pekhomDestinationRouteRepo)
    {
        $this->bdTicketClient = $bd_ticket;
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
        if(!$pick_up_location || !$destination_location)
            throw new InvalidLocationAddressException('Invalid pickup or destination id');
        $bd_ticket_vehicles =$this->parseBdTicketResponse($this->bdTicketClient->post('coaches/search',['date' => $this->date, 'fromStationId' => $pick_up_location->bd_ticket_id, 'toStationId' => $destination_location->bd_ticket_id]));
        $pekhom_vehicles = $this->parsePekhomResponse($this->pekhomClient->post('bus/search.php',['journey_date' => $this->date, 'from_location_uid' => $pick_up_location->pekhom_id, 'to_location_uid' => $destination_location->pekhom_id]));
        $vehicles = array_merge($bd_ticket_vehicles,$pekhom_vehicles);
        $filters = $this->getFilters($vehicles);
        $this->parseTags($vehicles);
        $vehicles = collect($vehicles);
//        $vehicles = $vehicles->unique('code')->values();
        return ['coaches' => $vehicles, 'filters' => $filters];;
    }

    private function parseBdTicketResponse($data)
    {
        $bd_ticket_vehicles = [];
        if($data['data']) {
            $vehicles = $data['data'];
            foreach ($vehicles as  $vehicle) {
                $vehicle = (object) $vehicle;
                $start_time =  $vehicle->departureTime;
                $end_time = $vehicle->arrivalTime;
                if(str_contains($start_time,'PM') && str_contains($end_time,'AM')) {
                    $date_next = Carbon::parse($this->date)->addDays(1)->format('Y-m-d').' '.$end_time;
                    $duration = Carbon::parse($this->date.' '.$start_time)->diffInMinutes(Carbon::parse($date_next));
                }
                else
                    $duration = Carbon::parse($this->date.' '.$start_time)->diffInMinutes(Carbon::parse($this->date.' '.$end_time));

                $current_vehicle_details = [
                    'id' => $vehicle->id,
                    'company_name' => ( (object) ($vehicle->company))->name,
                    'type' => $vehicle->coachType,
                    'start_time' => $start_time,
                    'start_point' => $vehicle->startCounter,
                    'end_time' => $end_time,
                    'end_point' => $vehicle->endCounter,
                    'price' => (double) $vehicle->minimumFare,
                    'seats_left' => $vehicle->availableSeats,
                    'code' => $vehicle->coachNo,
                    'vendor_id' => 1,
                    'duration' => $this->convertToHoursMins($duration)
                ];
                array_push($bd_ticket_vehicles, $current_vehicle_details);
            }
        }

        return $bd_ticket_vehicles;
    }

    private function parsePekhomResponse($data)
    {
        $pekhom_vehicles = [];
        if(isset($data['api_reponse']) && $data['api_reponse']['status'] === 'OK' ) {
            $vehicles = $data['api_data'];
            foreach ($vehicles as  $vehicle) {
                $vehicle = (object) $vehicle;
                $start_time =  $vehicle->departure_time;
                $end_time = $vehicle->arrival_time;
                if(str_contains($start_time,'PM') && str_contains($end_time,'AM')) {
                    $date_next = Carbon::parse($this->date)->addDays(1)->format('Y-m-d').' '.$end_time;
                    $duration = Carbon::parse($this->date.' '.$start_time)->diffInMinutes(Carbon::parse($date_next));
                }
                else
                    $duration = Carbon::parse($this->date.' '.$start_time)->diffInMinutes(Carbon::parse($this->date.' '.$end_time));

                $current_vehicle_details = [
                    'id' => $vehicle->bus_id,
                    'company_name' => ($vehicle->bus_name),
                    'type' => $vehicle->bus_type,
                    'start_time' => $start_time,
                    'start_point' => $vehicle->start_counter,
                    'end_time' => $end_time,
                    'end_point' => $vehicle->end_counter,
                    'price' => (double) $vehicle->price[$vehicle->seat_class[0]],
                    'seats_left' => $vehicle->available_seat,
                    'code' => $vehicle->bus_no,
                    'vendor_id' => 2,
                    'duration' => $this->convertToHoursMins($duration)
                ];
                array_push($pekhom_vehicles, $current_vehicle_details);
            }
        }
        return $pekhom_vehicles;
    }

    private function getFilters($vehicles)
    {
        $company_names = $shifts =  $types = $prices = [];
        foreach ($vehicles as $vehicle) {
            $vehicle = (object) $vehicle;
            $this->insertIntoArray($vehicle->company_name,  $company_names);
            if(strtolower($vehicle->type) === 'ac') $vehicle->type = 'AC';
            $this->insertIntoArray($vehicle->type,  $types);
            $this->insertIntoArray($this->parseTimeShift($vehicle->start_time),  $shifts);
        }
        return [
            'company_names' => $company_names,
            'shifts' => $shifts,
            'types' => $types
        ];
    }

    private function insertIntoArray($item, &$array)
    {
        if(!in_array($item, $array))
            array_push($array, $item);
    }

    private function parseTimeShift($time)
    {
        $hour = Carbon::parse($this->date." ".$time)->hour;
        if ($hour < 12) {
            return 'morning';
        } else
            if ($hour >= 12 && $hour < 18) {
                return 'evening';
            } else
                return 'night';
    }

    private function parseTags(&$vehicles)
    {
        foreach ($vehicles as  $index => $vehicle) {
            $type = $vehicle['type'];
            if(strtolower($vehicle->type) === 'ac') $type = 'AC';
            $tags = [
                'company_names' => [
                    $vehicle['company_name']
                ],
                'shifts' => [
                    $this->parseTimeShift($vehicle['start_time'])
                ],
                'types'  => [
                    $type
                ]
            ];
//            array_push($tags, $this->parseTimeShift($vehicle['start_time']));
//            array_push($tags, $vehicle['type']);
//            array_push($tags, $vehicle['company_name']);
            $vehicles[$index]['tags'] = $tags;
        }
    }

    private function convertToHoursMins($time, $format = '%02dh %02dm') {
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }
}