<?php

namespace Sheba\Checkout\Services;


use App\Exceptions\HyperLocationNotFoundException;
use App\Models\Thana;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use phpDocumentor\Reflection\Types\Parent_;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use stdClass;

class RentACarServiceObject extends ServiceObject
{

    protected function build()
    {
        $this->setPickUpProperties();
        $this->setDestinationProperties();
        $this->setDropProperties();
        parent::build(); // TODO: Change the autogenerated stub
    }

    private function setPickUpProperties()
    {
        if (isset($this->service->pick_up_location_geo)) {
            $geo = $this->service->pick_up_location_geo;
            $this->pickUpLocationLat = (double)$geo->lat;
            $this->pickUpLocationLng = (double)$geo->lng;
            $this->pickUpThana = $this->getThana($this->pickUpLocationLat, $this->pickUpLocationLng, Thana::all());
            if (!in_array($this->pickUpThana->district_id, config('sheba.rent_a_car_districts'))) throw new HyperLocationNotFoundException("You are out of service area");
            $this->pickUpLocationId = $this->pickUpThana->id;
            $this->pickUpLocationType = "App\\Models\\" . class_basename($this->pickUpThana);
        } else {
            $this->pickUpLocationId = (int)$this->service->pick_up_location_id;
            $this->pickUpLocationType = "App\\Models\\Thana";
            $this->pickUpAddress = $this->service->pick_up_address;
            $this->pickUpThana = ($this->pickUpLocationType)::find($this->pickUpLocationId);
            if (is_null($this->pickUpThana)) throw new HyperLocationNotFoundException("You are out of service area");
            $this->pickUpLocationLat = $this->pickUpThana->lat;
            $this->pickUpLocationLng = $this->pickUpThana->lng;
        }

    }

    private function setDestinationProperties()
    {
        if (isset($this->service->destination_location_geo)) {
            $geo = $this->service->destination_location_geo;
            $this->destinationLocationLat = (double)$geo->lat;
            $this->destinationLocationLng = (double)$geo->lng;
            $this->destinationThana = $this->getThana($this->destinationLocationLat, $this->destinationLocationLng, Thana::where('district_id', '<>', 1)->get());
            $this->destinationLocationId = $this->pickUpThana->id;
            $this->destinationLocationType = "App\\Models\\Thana";
        } elseif (isset($this->service->destination_location_id) && isset($this->service->destination_location_type)) {
            $this->destinationLocationId = (int)$this->service->destination_location_id;
            $this->destinationLocationType = "App\\Models\\" . $this->service->destination_location_type;
            $destination = ($this->destinationLocationType)::find($this->destinationLocationId);
            $this->destinationThana = $destination;
            $this->destinationLocationLat = $destination->lat;
            $this->destinationLocationLng = $destination->lng;
        }
        if (isset($this->service->destination_address)) $this->destinationAddress = $this->service->destination_address;
    }

    private function setDropProperties()
    {
        if (isset($this->service->drop_off_date)) $this->dropOffDate = $this->service->drop_off_date;
        if (isset($this->service->drop_off_time)) $this->dropOffTime = $this->service->drop_off_time;
    }

    protected function setQuantity()
    {
        parent::setQuantity();
        if (in_array($this->service->id, $this->googleCalculatedCarService)) {
            $data = $this->getDistanceCalculationResult();
            $this->quantity = (double)($data->rows[0]->elements[0]->distance->value) / 1000;
            $this->estimatedTime = (double)($data->rows[0]->elements[0]->duration->value) / 60;
            $this->estimatedDistance = $this->quantity;
        }
    }

    private function getDistanceCalculationResult()
    {
        $client = new Client();
        try {
            $res = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json',
                [
                    'query' => ['origins' => (string)$this->pickUpThana->lat . ',' . (string)$this->pickUpThana->lng,
                        'destinations' => (string)$this->destinationThana->lat . ',' . (string)$this->destinationThana->lng,
                        'key' => env('GOOGLE_DISTANCEMATRIX_KEY'), 'mode' => 'driving']
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return null;
        }
    }

    private function getThana($lat, $lng, $models)
    {
        $current = new Coords($lat, $lng);
        $to = $models->map(function ($model) {
            return new Coords(floatval($model->lat), floatval($model->lng), $model->id);
        })->toArray();
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $results = $distance->from([$current])->to($to)->sortedDistance()[0];
        $result = array_keys($results)[0];
        return $models->where('id', $result)->first();
    }
}