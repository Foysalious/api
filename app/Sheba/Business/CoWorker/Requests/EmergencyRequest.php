<?php namespace Sheba\Business\CoWorker\Requests;

use App\Models\BusinessMember;

class EmergencyRequest
{
    private $businessMember;
    private $name;
    private $mobile;
    private $relationship;

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember($business_member)
    {
        $this->businessMember = BusinessMember::findOrFail($business_member);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setEmergencyContractPersonName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContractPersonName()
    {
        return $this->name;
    }

    /**
     * @param $mobile
     * @return $this
     */
    public function setEmergencyContractPersonMobile($mobile)
    {
        $this->mobile = $mobile ? formatMobile($mobile) : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContractPersonMobile()
    {
        return $this->mobile;
    }

    /**
     * @param $relationship
     * @return $this
     */
    public function setRelationshipEmergencyContractPerson($relationship)
    {
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelationshipEmergencyContractPerson()
    {
        return $this->relationship;
    }
}