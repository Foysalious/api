<?php namespace Sheba\ServiceRequest;


use App\Models\Category;
use App\Models\Service;

class ServiceRequestObject
{

    private $serviceId;
    private $option;
    private $quantity;

    /** @var Service */
    private $service;
    /** @var Category */
    private $category;

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


    public function build()
    {
        $this->service = Service::find($this->serviceId);
        $this->category = $this->service->category;
        return $this;
    }
}
