<?php namespace Sheba\Business\Driver;

use Carbon\Carbon;
use Sheba\Helpers\Formatters\BDMobileFormatter;

class CreateRequest
{
    private $licenseNumber;
    private $licenseNumberEndDate;
    private $licenseClass;
    private $driverMobile;
    private $name;
    private $dateOfBirth;
    private $bloodGroup;
    private $nidNumber;
    private $department;
    private $vendorMobile;
    private $role;
    private $address;
    private $adminMember;

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
     * @param $license_number_end_date
     * @return $this
     */
    public function setLicenseNumberEndDate($license_number_end_date)
    {
        $this->licenseNumberEndDate = $license_number_end_date;
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
     * @param $driver_mobile
     * @return CreateRequest
     */
    public function setMobile($driver_mobile)
    {
        $this->driverMobile = BDMobileFormatter::format($driver_mobile);
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
    public function getLicenseNumberEndDate()
    {
        return $this->licenseNumberEndDate;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->driverMobile;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return CreateRequest
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param $date_of_birth
     * @return CreateRequest
     */
    public function setDateOfBirth($date_of_birth)
    {
        $this->dateOfBirth = Carbon::parse($date_of_birth);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBloodGroup()
    {
        return $this->bloodGroup;
    }

    /**
     * @param $blood_group
     * @return CreateRequest
     */
    public function setBloodGroup($blood_group)
    {
        $this->bloodGroup = $blood_group;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNidNumber()
    {
        return $this->nidNumber;
    }

    /**
     * @param $nid_number
     * @return CreateRequest
     */
    public function setNidNumber($nid_number)
    {
        $this->nidNumber = $nid_number;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param mixed $department
     * @return CreateRequest
     */
    public function setDepartment($department)
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorMobile()
    {
        return $this->vendorMobile;
    }

    /**
     * @param mixed $vendor_mobile
     * @return CreateRequest
     */
    public function setVendorMobile($vendor_mobile)
    {
        $this->vendorMobile = $vendor_mobile ? BDMobileFormatter::format($vendor_mobile) : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     * @return CreateRequest
     */
    public function setRole($role)
    {
        $this->role = $role;
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
     * @param mixed $address
     * @return CreateRequest
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminMember()
    {
        return $this->adminMember;
    }

    /**
     * @param mixed $admin_member
     * @return CreateRequest
     */
    public function setAdminMember($admin_member)
    {
        $this->adminMember = $admin_member;
        return $this;
    }
}