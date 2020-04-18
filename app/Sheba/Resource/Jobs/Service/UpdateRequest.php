<?php namespace Sheba\Resource\Jobs\Service;


use Sheba\ServiceRequest\ServiceRequest;

class UpdateRequest
{

    private $services;
    private $quantity;
    private $material;
    private $serviceRequest;

    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
    }

    /**
     * @param mixed $services
     * @return UpdateRequest
     */
    public function setServices($services)
    {
        $this->services = $this->serviceRequest->setServices(json_decode($services, 1))->get();
        return $this;
    }

    /**
     * @param mixed $quantity
     * @return UpdateRequest
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @param mixed $material
     * @return UpdateRequest
     */
    public function setMaterial($material)
    {
        $this->material = $material;
        return $this;
    }

    public function update()
    {
        dd($this->services);
    }


}