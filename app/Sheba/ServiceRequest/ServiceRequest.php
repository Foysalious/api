<?php namespace Sheba\ServiceRequest;


use Illuminate\Validation\ValidationException;

class ServiceRequest
{
    private $services;
    private $validator;
    /** @var ServiceRequestObject */
    private $serviceRequestObject;

    public function __construct(Validator $validator, ServiceRequestObject $service_request_object)
    {
        $this->validator = $validator;
        $this->validator = $validator;
        $this->serviceRequestObject = $service_request_object;
    }

    public function setServices(array $services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @return ServiceRequestObject[]
     * @throws ValidationException
     */
    public function get()
    {
        $this->validate();
        $final = [];
        foreach ($this->services as $service) {
            $this->serviceRequestObject->setServiceId($service['id'])->setQuantity($service['quantity'])->setOption($service['option'])->build();
            array_push($final, $this->serviceRequestObject);
        }
        return $final;
    }

    /**
     * @throws ValidationException
     */
    private function validate()
    {
        $this->validator->setServices($this->services)->validate();
        if ($this->validator->hasError()) throw new ValidationException($this->validator->getErrors());
    }
}