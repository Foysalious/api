<?php namespace Sheba\Business\Vehicle;

class CreateValidator
{
    /** @var CreateRequest $vehicleCreateRequest*/
    private $vehicleCreateRequest;

    public function setVehicleCreateRequest(CreateRequest $create_request)
    {
        $this->vehicleCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        if (false)
            return ['code' => 421, 'msg' => 'invalid.'];
    }
}