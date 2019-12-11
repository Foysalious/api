<?php namespace Sheba\ServiceRequest;


use Illuminate\Validation\ValidationException;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\Location\Geo;

class ServiceRequest
{
    private $services;
    private $validator;
    /** @var ServiceRequestObject */
    private $serviceRequestObject;

    public function __construct(Validator $validator, ServiceRequestObject $service_request_object)
    {
        $this->validator = $validator;
        $this->serviceRequestObject = $service_request_object;
    }

    public function setServices(array $services)
    {
        $this->services = $services;
        return $this;
    }

    public function get()
    {
        $this->validate();
        $final = [];
        foreach ($this->services as $service) {
            $this->serviceRequestObject->setServiceId($service['id'])->setQuantity($service['quantity'])->setOption(array_map('intval',$service['option']));
            if (isset($service['pick_up_location_geo'])) {
                $geo = new Geo();
                $this->serviceRequestObject->setPickUpGeo($geo->setLat($service['pick_up_location_geo']['lat'])->setLng($service['pick_up_location_geo']['lng']));
            };
            if (isset($service['destination_location_geo'])) {
                $geo = new Geo();
                $this->serviceRequestObject->setDestinationGeo($geo->setLat($service['destination_location_geo']['lat'])->setLng($service['destination_location_geo']['lng']));
            }
            if (isset($service['pick_up_address'])) $this->serviceRequestObject->setPickUpAddress($service['pick_up_address']);
            if (isset($service['destination_address'])) $this->serviceRequestObject->setDestinationAddress($service['destination_address']);
            if (isset($service['drop_off_date'])) $this->serviceRequestObject->setDropOffDate($service['drop_off_date']);
            if (isset($service['drop_off_time'])) $this->serviceRequestObject->setDropOffTime($service['drop_off_time']);
            $this->serviceRequestObject->build();
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