<?php namespace Sheba\Business\Vendor;

use Sheba\ModificationFields;
use DB;

class Creator
{
    use ModificationFields;

    /** @var CreateRequest $vendorCreateRequest */
    private $vendorCreateRequest;
    /** @var CreateValidator $validator */
    private $validator;

    public function __construct(CreateValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setVendorCreateRequest(CreateRequest $create_request)
    {
        $this->vendorCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        $this->validator->setVendorCreateRequest($this->vendorCreateRequest);
        return $this->validator->hasError();
    }

    public function create()
    {
        DB::transaction(function () {

        });
    }

    private function formatVehicleRegistrationInfoSpecificData()
    {

    }
}