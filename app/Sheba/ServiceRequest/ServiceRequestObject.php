<?php namespace Sheba\ServiceRequest;


use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Models\Category;
use App\Models\Service;
use App\Models\Thana;
use Illuminate\Database\Eloquent\Collection;
use Sheba\Google\MapClient;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\Location\Geo;

class ServiceRequestObject
{

    private $serviceId;
    private $option;
    private $quantity;
    /** @var Geo */
    private $pickUpGeo;
    private $pickUpAddress;
    /** @var Geo */
    private $destinationGeo;
    private $destinationAddress;
    private $dropOffDate;
    private $dropOffTime;
    /** @var Thana */
    private $pickUpThana;
    private $thanas;


    /** @var Thana */
    private $destinationThana;
    private $estimatedDistance;
    private $estimatedTime;
    private $insideCityCategoryId;
    private $outsideCityCategoryId;
    private $googleCalculatedCarService;
    private $mapClient;

    /** @var Service */
    private $service;
    /** @var Category */
    private $category;


    public function __construct()
    {
        $this->insideCityCategoryId = config('sheba.rent_a_car')['inside_city']['category'];
        $this->outsideCityCategoryId = config('sheba.rent_a_car')['outside_city']['category'];
        $this->googleCalculatedCarService = config('sheba.car_rental.destination_fields_service_ids');
        $this->mapClient = new MapClient();
    }

    /**
     * @return mixed
     */
    public function getEstimatedDistance()
    {
        return $this->estimatedDistance;
    }

    /**
     * @return mixed
     */
    public function getEstimatedTime()
    {
        return $this->estimatedTime;
    }

    /**
     * @return mixed
     */
    public function getDropOffDate()
    {
        return $this->dropOffDate;
    }

    /**
     * @param mixed $dropOffDate
     * @return ServiceRequestObject
     */
    public function setDropOffDate($dropOffDate)
    {
        $this->dropOffDate = $dropOffDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDropOffTime()
    {
        return $this->dropOffTime;
    }

    /**
     * @param mixed $dropOffTime
     * @return ServiceRequestObject
     */
    public function setDropOffTime($dropOffTime)
    {
        $this->dropOffTime = $dropOffTime;
        return $this;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function setServiceId($service_id)
    {
        $this->serviceId = $service_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param mixed $option
     * @return ServiceRequestObject
     */
    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     * @return ServiceRequestObject
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function setPickUpGeo(Geo $geo)
    {
        $this->pickUpGeo = $geo;
        return $this;
    }

    public function setDestinationGeo(Geo $geo)
    {
        $this->destinationGeo = $geo;
        return $this;
    }


    /**
     * @return $this
     * @throws DestinationCitySameAsPickupException
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     * @throws ServiceIsUnpublishedException
     */
    public function build()
    {
        $this->service = Service::where('id', $this->serviceId)->publishedForAll()->first();
        if (!$this->service) throw new ServiceIsUnpublishedException('Service #' . $this->serviceId . " is not available.", 400);
        $this->category = $this->service->category;
        if ($this->category->isRentACar()) {
            $this->setThanas();
            $this->setPickupThana();
            $this->setDestinationThana();
            if (in_array($this->service->id, $this->googleCalculatedCarService)) $this->quantity = $this->getDistanceCalculationResult();
        }
        return $this;
    }

    /**
     * @return Thana
     */
    public function getPickupThana()
    {
        return $this->pickUpThana;
    }

    /**
     * @return Thana
     */
    public function getDestinationThana()
    {
        return $this->destinationThana;
    }

    /**
     * @return Geo
     */
    public function getPickUpGeo()
    {
        return $this->pickUpGeo;
    }

    /**
     * @param mixed $pickUpAddress
     * @return ServiceRequestObject
     */
    public function setPickUpAddress($pickUpAddress)
    {
        $this->pickUpAddress = $pickUpAddress;
        return $this;
    }

    /**
     * @param mixed $destinationAddress
     * @return ServiceRequestObject
     */
    public function setDestinationAddress($destinationAddress)
    {
        $this->destinationAddress = $destinationAddress;
        return $this;
    }

    /**
     * @return Geo
     */
    public function getDestinationGeo()
    {
        return $this->destinationGeo;
    }

    /**
     * @return mixed
     */
    public function getPickUpAddress()
    {
        return $this->pickUpAddress;
    }

    /**
     * @return mixed
     */
    public function getDestinationAddress()
    {
        return $this->destinationAddress;
    }

    private function setThanas()
    {
        $this->thanas = Thana::all();
        return $this;
    }

    /**
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     */
    private function setPickupThana()
    {
        if (!$this->pickUpGeo) return;
        $this->pickUpThana = $this->getThana($this->pickUpGeo->getLat(), $this->pickUpGeo->getLng());
        if (!in_array($this->pickUpThana->district_id, config('sheba.rent_a_car_pickup_district_ids'))) {
            if (!in_array($this->getCategory()->id, $this->outsideCityCategoryId)) {
                throw new InsideCityPickUpAddressNotFoundException("Got " . $this->pickUpThana->name . '(' . $this->pickUpThana->id . ') for pickup');
            }
            throw new OutsideCityPickUpAddressNotFoundException("Got " . $this->pickUpThana->name . '(' . $this->pickUpThana->id . ') for pickup');
        }
    }

    /**
     * @throws DestinationCitySameAsPickupException
     */
    private function setDestinationThana()
    {
        if (!$this->destinationGeo || in_array($this->service->id, [1043, 1044])) return;
        $this->destinationThana = $this->getThana($this->destinationGeo->getLat(), $this->destinationGeo->getLng());
        if ($this->pickUpThana->district_id == $this->destinationThana->district_id) {
            throw new DestinationCitySameAsPickupException("Got " . $this->destinationThana->name . '(' . $this->destinationThana->id . ') for destination');
        }
    }

    private function getThana($lat, $lng)
    {
        $current = new Coords($lat, $lng);
        $to = $this->thanas->map(function ($model) {
            return new Coords(floatval($model->lat), floatval($model->lng), $model->id);
        })->toArray();
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $results = $distance->from([$current])->to($to)->sortedDistance()[0];
        $result = array_keys($results)[0];
        return $this->thanas->where('id', $result)->first();
    }

    private function getDistanceCalculationResult()
    {
        $data = $this->mapClient->getDistanceBetweenTwoPints($this->pickUpGeo, $this->destinationGeo);
        $this->estimatedTime = (double)($data->rows[0]->elements[0]->duration->value) / 60;
        $this->estimatedDistance = $this->quantity;
        return (double)($data->rows[0]->elements[0]->distance->value) / 1000;
    }
}
