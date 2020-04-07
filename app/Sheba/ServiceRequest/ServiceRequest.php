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

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function setServices(array $services)
    {
        $this->services = $services;
        return $this;
    }


    /**
     * @return  ServiceRequestObject[]
     * @throws ServiceIsUnpublishedException
     * @throws ValidationException
     * @throws \App\Exceptions\RentACar\DestinationCitySameAsPickupException
     * @throws \App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException
     * @throws \App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException
     */
    public function get()
    {
        $this->validate();
        $final = [];
        foreach ($this->services as $service) {
            $serviceRequestObject = new ServiceRequestObject();
            $serviceRequestObject->setServiceId($service['id'])->setQuantity($service['quantity']);
            if (isset($service['pick_up_location_geo'])) {
                $geo = new Geo();
                $serviceRequestObject->setPickUpGeo($geo->setLat($service['pick_up_location_geo']['lat'])->setLng($service['pick_up_location_geo']['lng']));
            };
            if (isset($service['destination_location_geo'])) {
                $geo = new Geo();
                $serviceRequestObject->setDestinationGeo($geo->setLat($service['destination_location_geo']['lat'])->setLng($service['destination_location_geo']['lng']));
            }
            if (isset($service['pick_up_address'])) $serviceRequestObject->setPickUpAddress($service['pick_up_address']);
            if (isset($service['destination_address'])) $serviceRequestObject->setDestinationAddress($service['destination_address']);
            if (isset($service['drop_off_date'])) $serviceRequestObject->setDropOffDate($service['drop_off_date']);
            if (isset($service['drop_off_time'])) $serviceRequestObject->setDropOffTime($service['drop_off_time']);
            if (isset($service['option'])) $serviceRequestObject->setOption(array_map('intval', $service['option']));
            $serviceRequestObject->build();
            array_push($final, $serviceRequestObject);
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