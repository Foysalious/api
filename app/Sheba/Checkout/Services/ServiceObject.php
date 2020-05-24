<?php

namespace Sheba\Checkout\Services;

use App\Models\Category;
use App\Models\Service;
use Sheba\ServiceRequest\Exception\ServiceIsUnpublishedException;
use stdClass;

class ServiceObject
{
    protected $id;
    protected $option;
    protected $quantity;

    protected $pickUpLocationId;
    protected $pickUpLocationType;
    protected $pickUpLocationLat;
    protected $pickUpLocationLng;
    protected $pickUpAddress;
    protected $pickUpThana;

    protected $destinationLocationId;
    protected $destinationLocationType;
    protected $destinationLocationLat;
    protected $destinationLocationLng;
    protected $destinationThana;

    protected $destinationAddress;
    protected $estimatedDistance;
    protected $estimatedTime;
    protected $dropOffDate;
    protected $dropOffTime;
    /**@var Service* */
    protected $serviceModel;
    protected $service;
    protected $googleCalculatedCarService;

    public function __construct(stdClass $service)
    {
        $this->service = $service;
        $this->googleCalculatedCarService = config('sheba.car_rental')['destination_fields_service_ids'];
        $this->build();
        $this->setQuantity();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    protected function build()
    {
        $this->setService();
        $this->setCommonObject();
    }

    public function setCommonObject()
    {
        $this->id = (int)$this->service->id;
        $this->option = $this->serviceModel->isOptions() ? array_map('intval', $this->service->option) : [];
    }

    public function setService()
    {
        $this->serviceModel = Service::with('subscription')->where('id', $this->service->id)->publishedForAll()->first();
        if (!$this->serviceModel) throw new ServiceIsUnpublishedException('Service #' . $this->service->id . " is not available.", 400);
    }

    protected function setQuantity()
    {
        if (isset($this->service->quantity)) {
            $quantity = (double)$this->service->quantity;
            $min_quantity = (double)$this->serviceModel->min_quantity;
            $this->quantity = $quantity >= $min_quantity ? $quantity : $min_quantity;
        } else
            $this->quantity = (double)$this->serviceModel->min_quantity;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->serviceModel;
    }

    public function getOption()
    {
        return $this->option;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->serviceModel->category;
    }
}