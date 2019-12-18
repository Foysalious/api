<?php namespace Sheba\Business\Vehicle;

use Carbon\Carbon;
use Sheba\Helpers\Formatters\BDMobileFormatter;

class CreateRequest
{
    private $vehicleType;
    private $vehicleBrandName;
    private $modelName;
    private $modelYear;
    private $vehicleDepartment;
    private $seatCapacity;
    private $vendorPhoneNumber;
    private $licenseNumber;
    private $licenseNumberEndDate;
    private $taxTokenNumber;
    private $fitnessValidityStart;
    private $fitnessValidityEnd;
    private $insuranceValidTill;
    private $transmissionType;
    private $adminMember;
    private $business;

    /**
     * @return mixed
     */
    public function getVehicleType()
    {
        return $this->vehicleType;
    }

    /**
     * @param mixed $vehicle_type
     * @return CreateRequest
     */
    public function setVehicleType($vehicle_type)
    {
        $this->vehicleType = $vehicle_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVehicleBrandName()
    {
        return $this->vehicleBrandName;
    }

    /**
     * @param mixed $vehicle_brand_name
     * @return CreateRequest
     */
    public function setVehicleBrandName($vehicle_brand_name)
    {
        $this->vehicleBrandName = $vehicle_brand_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @param mixed $model_name
     * @return CreateRequest
     */
    public function setModelName($model_name)
    {
        $this->modelName = $model_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModelYear()
    {
        return $this->modelYear;
    }

    /**
     * @param mixed $model_year
     * @return CreateRequest
     */
    public function setModelYear($model_year)
    {
        $this->modelYear = Carbon::parse($model_year);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVehicleDepartment()
    {
        return $this->vehicleDepartment;
    }

    /**
     * @param mixed $vehicle_department
     * @return CreateRequest
     */
    public function setVehicleDepartment($vehicle_department)
    {
        $this->vehicleDepartment = $vehicle_department;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeatCapacity()
    {
        return $this->seatCapacity;
    }

    /**
     * @param mixed $seat_capacity
     * @return CreateRequest
     */
    public function setSeatCapacity($seat_capacity)
    {
        $this->seatCapacity = $seat_capacity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorPhoneNumber()
    {
        return $this->vendorPhoneNumber;
    }

    /**
     * @param mixed $vendor_phone_number
     * @return CreateRequest
     */
    public function setVendorPhoneNumber($vendor_phone_number)
    {
        $this->vendorPhoneNumber = $vendor_phone_number ? BDMobileFormatter::format($vendor_phone_number) : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLicenseNumber()
    {
        return $this->licenseNumber;
    }

    /**
     * @param mixed $license_number
     * @return CreateRequest
     */
    public function setLicenseNumber($license_number)
    {
        $this->licenseNumber = $license_number;
        return $this;
    }

    /**
     * @param $license_number_end_date
     * @return CreateRequest
     */
    public function setLicenseNumberEndDate($license_number_end_date)
    {
        $this->licenseNumberEndDate = $license_number_end_date;
        return $this;
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
    public function getTaxTokenNumber()
    {
        return $this->taxTokenNumber;
    }

    /**
     * @param mixed $tax_token_number
     * @return CreateRequest
     */
    public function setTaxTokenNumber($tax_token_number)
    {
        $this->taxTokenNumber = $tax_token_number;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFitnessValidityStart()
    {
        return $this->fitnessValidityStart;
    }

    /**
     * @param mixed $fitness_validity_start
     * @return CreateRequest
     */
    public function setFitnessValidityStart($fitness_validity_start)
    {
        $this->fitnessValidityStart = $fitness_validity_start;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFitnessValidityEnd()
    {
        return $this->fitnessValidityEnd;
    }

    /**
     * @param mixed $fitness_validity_end
     * @return CreateRequest
     */
    public function setFitnessValidityEnd($fitness_validity_end)
    {
        $this->fitnessValidityEnd = $fitness_validity_end;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInsuranceValidTill()
    {
        return $this->insuranceValidTill;
    }

    /**
     * @param mixed $insurance_valid_till
     * @return CreateRequest
     */
    public function setInsuranceValidTill($insurance_valid_till)
    {
        $this->insuranceValidTill = $insurance_valid_till;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransmissionType()
    {
        return $this->transmissionType;
    }

    /**
     * @param mixed $transmission_type
     * @return CreateRequest
     */
    public function setTransmissionType($transmission_type)
    {
        $this->transmissionType = $transmission_type;
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

    /**
     * @return mixed
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * @param mixed $business
     * @return CreateRequest
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }
}