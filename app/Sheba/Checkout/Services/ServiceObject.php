<?php

namespace Sheba\Checkout\Services;

use App\Models\Service;
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

    public function __construct(stdClass $service)
    {
        $this->service = $service;
        $this->build();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    protected function build()
    {
        $this->setCommonObject();
        $this->setService();
        $this->setQuantity();
    }

    public function setCommonObject()
    {
        $this->id = (int)$this->service->id;
        $this->option = array_map('intval', $this->service->option);
    }

    public function setService()
    {
        $this->serviceModel = Service::where('id', $this->service->id)->publishedForAll()->first();
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
}