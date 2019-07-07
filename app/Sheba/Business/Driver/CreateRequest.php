<?php namespace Sheba\Business\Driver;

class CreateRequest
{
    private $licenseNumber;
    private $licenseClass;
    private $driverMobile;

    /**
     * @param $license_number
     * @return $this
     */
    public function setLicenseNumber($license_number)
    {
        $this->licenseNumber = $license_number;
        return $this;
    }

    /**
     * @param $license_class
     * @return $this
     */
    public function setLicenseClass($license_class)
    {
        $this->licenseClass = $license_class;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLicenseClass()
    {
        return $this->licenseClass;
    }

    /**
     * @return mixed
     */
    public function getLicenseNumber()
    {
        return $this->licenseNumber;
    }

    /**
     * @return mixed
     */
    public function getDriverMobile()
    {
        return $this->driverMobile;
    }

    /**
     * @param $driver_mobile
     * @return CreateRequest
     */
    public function setDriverMobile($driver_mobile)
    {
        $this->driverMobile = $driver_mobile;
        return $this;
    }
}