<?php namespace Sheba\Business;

use App\Models\Business;
use Sheba\ModificationFields;

class BusinessUpdater
{
    use ModificationFields;

    /** @var BusinessCreatorRequest $businessCreatorRequest */
    private $businessCreatorRequest;
    /** @var Business $business */
    private $business;

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
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @return bool|int
     */
    public function update()
    {
        return $this->business->update($this->withUpdateModificationField($this->getBusinessData()));
    }

    public function updateLogo()
    {
        return $this->business->update($this->withUpdateModificationField(['logo' => $this->businessCreatorRequest->getLogoUrl()]));
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
            'phone' => $this->businessCreatorRequest->getPhone()
        ];
    }
}