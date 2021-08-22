<?php namespace Sheba\Business;

use App\Models\Business;

class BusinessCreatorRequest
{
    private $name;
    private $noEmployee;
    private $geoInformation;
    private $address;
    private $mobile;
    private $logoUrl;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmployeeSize()
    {
        return $this->noEmployee;
    }

    /**
     * @param $no_employee
     * @return $this
     */
    public function setEmployeeSize($no_employee)
    {
        $this->noEmployee = $no_employee;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGeoInformation()
    {
        return $this->geoInformation;
    }

    /**
     * @param $geoInformation
     * @return $this
     */
    public function setGeoInformation($geoInformation)
    {
        $this->geoInformation = $geoInformation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->mobile;
    }

    /**
     * @param $mobile
     * @return $this
     */
    public function setPhone($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return string|string[]|null
     */
    public function getSubDomain()
    {
        $blacklist = ["google", "facebook", "microsoft", "sheba", "sheba.xyz"];
        $base_name = $name = preg_replace('/-$/', '', substr(strtolower(clean($this->getName())), 0, 15));
        $already_used = Business::select('sub_domain')->pluck('sub_domain')->toArray();
        $counter = 0;
        while (in_array($name, array_merge($blacklist, $already_used))) {
            $name = $base_name . $counter;
            $counter++;
        }
        return $name;
    }

    public function setLogoUrl($logo_url)
    {
        $this->logoUrl = $logo_url;
        return $this;
    }

    public function getLogoUrl()
    {
        return $this->logoUrl;
    }
}