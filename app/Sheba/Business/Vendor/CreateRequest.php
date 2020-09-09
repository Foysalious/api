<?php namespace Sheba\Business\Vendor;

use Sheba\Helpers\Formatters\BDMobileFormatter;

class CreateRequest
{
    private $business;
    private $vendorName;
    private $vendorMobile;
    private $vendorEmail;
    private $vendorImage;
    private $vendorAddress;
    private $vendorMasterCategories;
    private $resourceName;
    private $resourceMobile;
    private $tradeLicenseNumber;
    private $tradeLicenseDocument;
    private $vatRegistrationNumber;
    private $vatRegistrationDocument;
    private $resourceNidNumber;
    private $resourceNidFront;
    private $resourceNidBack;
    private $isActiveForB2b;

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

    /**
     * @return mixed
     */
    public function getVendorName()
    {
        return $this->vendorName;
    }

    /**
     * @param mixed $vendorName
     * @return CreateRequest
     */
    public function setVendorName($vendorName)
    {
        $this->vendorName = $vendorName;
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
     * @param mixed $vendorMobile
     * @return CreateRequest
     */
    public function setVendorMobile($vendorMobile)
    {
        $this->vendorMobile = $vendorMobile ? BDMobileFormatter::format($vendorMobile) : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceNidFront()
    {
        return $this->resourceNidFront;
    }

    /**
     * @param $resourceNidFront
     * @return CreateRequest
     */
    public function setResourceNidFront($resourceNidFront)
    {
        $this->resourceNidFront = $resourceNidFront;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceNidBack()
    {
        return $this->resourceNidBack;
    }

    /**
     * @param $resourceNidBack
     * @return CreateRequest
     */
    public function setResourceNidBack($resourceNidBack)
    {
        $this->resourceNidBack = $resourceNidBack;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceNidNumber()
    {
        return $this->resourceNidNumber;
    }

    /**
     * @param mixed $resourceNidNumber
     * @return CreateRequest
     */
    public function setResourceNidNumber($resourceNidNumber)
    {
        $this->resourceNidNumber = $resourceNidNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVatRegistrationDocument()
    {
        return $this->vatRegistrationDocument;
    }

    /**
     * @param mixed $vatRegistrationDocument
     * @return CreateRequest
     */
    public function setVatRegistrationDocument($vatRegistrationDocument)
    {
        $this->vatRegistrationDocument = $vatRegistrationDocument;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVatRegistrationNumber()
    {
        return $this->vatRegistrationNumber;
    }

    /**
     * @param mixed $vatRegistrationNumber
     * @return CreateRequest
     */
    public function setVatRegistrationNumber($vatRegistrationNumber)
    {
        $this->vatRegistrationNumber = $vatRegistrationNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeLicenseDocument()
    {
        return $this->tradeLicenseDocument;
    }

    /**
     * @param mixed $tradeLicenseDocument
     * @return CreateRequest
     */
    public function setTradeLicenseDocument($tradeLicenseDocument)
    {
        $this->tradeLicenseDocument = $tradeLicenseDocument;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeLicenseNumber()
    {
        return $this->tradeLicenseNumber;
    }

    /**
     * @param mixed $tradeLicenseNumber
     * @return CreateRequest
     */
    public function setTradeLicenseNumber($tradeLicenseNumber)
    {
        $this->tradeLicenseNumber = $tradeLicenseNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceMobile()
    {
        return $this->resourceMobile;
    }

    /**
     * @param mixed $resourceMobile
     * @return CreateRequest
     */
    public function setResourceMobile($resourceMobile)
    {
        $this->resourceMobile = BDMobileFormatter::format($resourceMobile);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @param mixed $resourceName
     * @return CreateRequest
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorMasterCategories()
    {
        return $this->vendorMasterCategories;
    }

    /**
     * @param mixed $vendorMasterCategories
     * @return CreateRequest
     */
    public function setVendorMasterCategories($vendorMasterCategories)
    {
        $this->vendorMasterCategories = $vendorMasterCategories;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorAddress()
    {
        return $this->vendorAddress;
    }

    /**
     * @param mixed $vendorAddress
     * @return CreateRequest
     */
    public function setVendorAddress($vendorAddress)
    {
        $this->vendorAddress = $vendorAddress;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorImage()
    {
        return $this->vendorImage;
    }

    /**
     * @param mixed $vendorImage
     * @return CreateRequest
     */
    public function setVendorImage($vendorImage)
    {
        $this->vendorImage = $vendorImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorEmail()
    {
        return $this->vendorEmail;
    }

    /**
     * @param mixed $vendorEmail
     * @return CreateRequest
     */
    public function setVendorEmail($vendorEmail)
    {
        $this->vendorEmail = $vendorEmail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsActiveForB2b()
    {
        return $this->isActiveForB2b;
    }

    /**
     * @param $is_active_for_b2b
     * @return $this
     */
    public function setIsActiveForB2b($is_active_for_b2b)
    {
        $this->isActiveForB2b = $is_active_for_b2b;
        return $this;
    }
}