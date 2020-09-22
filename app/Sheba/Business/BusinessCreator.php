<?php namespace Sheba\Business;

use Sheba\Business\BusinessCreatorRequest;
use Sheba\ModificationFields;
use App\Models\Business;

class BusinessCreator
{
    use ModificationFields;

    /** @var BusinessCreatorRequest $businessCreatorRequest */
    private $businessCreatorRequest;

    /**
     * @param BusinessCreatorRequest $business_creator_request
     * @return $this
     */
    public function setBusinessCreatorRequest(BusinessCreatorRequest $business_creator_request)
    {
        $this->businessCreatorRequest = $business_creator_request;
        return $this;
    }

    /**
     * @return Business
     */
    public function create()
    {
        $business = Business::create($this->withCreateModificationField($this->getBusinessData()));
        return $business;
    }

    /**
     * @return array
     */
    private function getBusinessData()
    {
        return [
            'name' => $this->businessCreatorRequest->getName(),
            'employee_size' => $this->businessCreatorRequest->getEmployeeSize(),
            'geo_informations' => $this->businessCreatorRequest->getGeoInformation(),
            'address' => $this->businessCreatorRequest->getAddress(),
            'phone' => $this->businessCreatorRequest->getPhone(),
            'sub_domain' => $this->businessCreatorRequest->getSubDomain(),
        ];
    }
}