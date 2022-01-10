<?php namespace Sheba\ServiceRequest;


use App\Exceptions\HyperLocationNotFoundException;
use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use Sheba\Dal\Category\Category;
use App\Models\HyperLocal;
use Sheba\Dal\Service\Service;
use App\Models\Thana;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\Location\FromGeo;
use Sheba\Location\Geo;
use Sheba\Map\DistanceMatrix;
use Sheba\Map\MapClientNoResultException;
use Sheba\ServiceRequest\Exception\ServiceIsUnpublishedException;

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
    /** @var DistanceMatrix */
    private $distanceMatrix;

    /** @var Service */
    private $service;
    /** @var Category */
    private $category;
    /** @var HyperLocal */
    private $hyperLocal;
    /** @var FromGeo */
    private $fromGeo;

    public function __construct(DistanceMatrix $distance_matrix, FromGeo $from_geo)
    {
        $this->insideCityCategoryId = config('sheba.rent_a_car')['inside_city']['category'];
        $this->outsideCityCategoryId = config('sheba.rent_a_car')['outside_city']['category'];
        $this->googleCalculatedCarService = config('sheba.car_rental')['destination_fields_service_ids'];
        $this->distanceMatrix = $distance_matrix;
        $this->fromGeo = $from_geo;
    }

    /**
     * @return HyperLocal
     */
    public function getHyperLocal()
    {
        return $this->hyperLocal;
    }

    /**
     * @param HyperLocal $hyperLocal
     * @return ServiceRequestObject
     */
    public function setHyperLocal($hyperLocal)
    {
        $this->hyperLocal = $hyperLocal;
        return $this;
    }

    private function setEstimatedDistance($distance)
    {
        $this->estimatedDistance = $distance;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEstimatedDistance()
    {
        return $this->estimatedDistance;
    }

    private function setEstimatedTime($time)
    {
        $this->estimatedTime = $time;
        return $this;
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
     * @throws HyperLocationNotFoundException
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     * @throws ServiceIsUnpublishedException
     * @throws GuzzleException
     * @throws MapClientNoResultException
     */
    public function build()
    {
        $this->service = Service::where('id', $this->serviceId)->publishedForAll()->first();
        if (!$this->service) throw new ServiceIsUnpublishedException('Service #' . $this->serviceId . " is not available.", 400);
        $this->category = $this->service->category;
        $this->fromGeo->setThanas();
        $this->setPickupThana();
        $this->setDestinationThana();
        if (in_array($this->service->id, $this->googleCalculatedCarService)) $this->calculateDistanceResult();
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

    /**
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     * @throws HyperLocationNotFoundException
     */
    private function setPickupThana()
    {
        if (!$this->pickUpGeo) return;
        $this->calculateHyperLocalFromPickUpGeo();
        $this->pickUpThana = $this->fromGeo->getThanaFromGeo($this->pickUpGeo);
        if (!in_array($this->pickUpThana->district_id, config('sheba.rent_a_car_pickup_district_ids'))) {
            if (!in_array($this->getCategory()->id, $this->outsideCityCategoryId)) {
                throw new InsideCityPickUpAddressNotFoundException("Got " . $this->pickUpThana->name . '(' . $this->pickUpThana->id . ') for pickup');
            }
            throw new OutsideCityPickUpAddressNotFoundException("Got " . $this->pickUpThana->name . '(' . $this->pickUpThana->id . ') for pickup');
        }
    }

    /**
     * @throws HyperLocationNotFoundException
     */
    private function calculateHyperLocalFromPickUpGeo()
    {
        $hyper_local = HyperLocal::insidePolygon($this->pickUpGeo->getLat(), $this->pickUpGeo->getLng())->with('location')->first();
        if (!$hyper_local || !$hyper_local->location || !$hyper_local->location->isPublished()) throw new HyperLocationNotFoundException('This pickup address is out of our service area', 701);
        $this->setHyperLocal($hyper_local);
    }

    /**
     * @throws DestinationCitySameAsPickupException
     */
    private function setDestinationThana()
    {
        if (!$this->destinationGeo || in_array($this->service->id, [1043, 1044])) return;
        $this->destinationThana = $this->fromGeo->getThanaFromGeo($this->destinationGeo);
        if ($this->pickUpThana && $this->destinationThana && $this->pickUpThana->district_id == $this->destinationThana->district_id) {
            throw new DestinationCitySameAsPickupException("Got " . $this->destinationThana->name . '(' . $this->destinationThana->id . ') for destination');
        }
    }

    /**
     * @throws GuzzleException
     * @throws MapClientNoResultException
     */
    private function calculateDistanceResult()
    {
        $distance = $this->distanceMatrix->getDistanceMatrix($this->pickUpGeo, $this->destinationGeo);
        $this->setEstimatedTime($distance->getDurationInMinutes());
        $this->setEstimatedDistance($distance->getDistanceInKms());
        $this->setQuantity(1);
    }
}
