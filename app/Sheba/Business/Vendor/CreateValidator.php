<?php namespace Sheba\Business\Vendor;

class CreateValidator
{
    /** @var CreateRequest $vehicleCreateRequest*/
    private $vehicleCreateRequest;

    public function setVendorCreateRequest(CreateRequest $create_request)
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