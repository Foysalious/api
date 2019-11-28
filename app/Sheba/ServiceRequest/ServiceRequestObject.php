<?php namespace Sheba\ServiceRequest;


use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Models\Category;
use App\Models\Service;
use App\Models\Thana;
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
    /** @var Geo */
    private $destinationGeo;

    /** @var Service */
    private $service;
    /** @var Category */
    private $category;
    /** @var Thana */
    private $pickUpThana;
    /** @var Thana */
    private $destinationThana;
    private $insideCityCategoryId;
    private $outsideCityCategoryId;
    private $googleCalculatedCarService;
    private $mapClient;

    public function __construct()
    {
        $this->insideCityCategoryId = config('sheba.rent_a_car')['inside_city']['category'];
        $this->outsideCityCategoryId = config('sheba.rent_a_car')['outside_city']['category'];
        $this->googleCalculatedCarService = array_map('intval', explode(',', env('RENT_CAR_SERVICE_IDS')));
        $this->mapClient = new MapClient();
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
     */
    public function build()
    {
        $this->service = Service::find($this->serviceId);
        $this->category = $this->service->category;
        $this->setPickupThana();
        $this->setDestinationThana();
        if (in_array($this->service->id, $this->googleCalculatedCarService)) $this->quantity = $this->getDistanceCalculationResult();
        return $this;
    }

    /**
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     */
    private function setPickupThana()
    {
        if (!$this->pickUpGeo) return;
        $this->pickUpThana = $this->getThana($this->pickUpGeo->getLat(), $this->pickUpGeo->getLng(), Thana::all());
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
        if (in_array($this->service->id, [1043, 1044])) return;
        $this->destinationThana = $this->getThana($this->destinationGeo->getLat(), $this->destinationGeo->getLng(), Thana::all());
        if ($this->pickUpThana->district_id == $this->destinationThana->district_id) {
            throw new DestinationCitySameAsPickupException("Got " . $this->destinationThana->name . '(' . $this->destinationThana->id . ') for destination');
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

    private function getDistanceCalculationResult()
    {
        $data = $this->mapClient->getDistanceBetweenTwoPints($this->pickUpGeo, $this->destinationGeo);
        return (double)($data->rows[0]->elements[0]->distance->value) / 1000;
    }
}
