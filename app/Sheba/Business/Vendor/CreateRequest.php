<?php namespace Sheba\Business\Vendor;

use App\Models\PartnerBasicInformation;
use App\Models\Profile;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class CreateRequest
{
    use HasErrorCodeAndMessage;

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
    private $profileRepository;

    /**
     * ProfileCreateRequest constructor.
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(ProfileRepositoryInterface $profileRepository)
    {
        $this->profileRepository = $profileRepository;
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
        $this->checkUsedNidNumber();
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
        $this->checkUsedVatRegistrationNumber();
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

        $this->checkUsedTradeLicenseNumber();
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
        $this->checkUsedNumber();
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

    /**
     * @return $this
     */
    private function checkUsedNumber()
    {
        $profile = $this->profileRepository->checkExistingMobile($this->resourceMobile);
        if ($profile) $this->setError(409, "SP phone number is already exist, please try with another number");
        return $this;
    }

    /**
     * @return $this
     */
    private function checkUsedNidNumber()
    {
        if ($this->isNull($this->resourceNidNumber)) return $this;
        $profile = $this->profileRepository->checkExistingNid($this->resourceNidNumber);
        if ($profile) $this->setError(409, "NID number is already exist, please try with another number");
        return $this;
    }

    /**
     * @return $this
     */
    private function checkUsedVatRegistrationNumber()
    {
        if ($this->isNull($this->vatRegistrationNumber)) return $this;
        $partner_basic_info = PartnerBasicInformation::where('vat_registration_number', $this->vatRegistrationNumber)->first();
        if ($partner_basic_info) $this->setError(409, "VAT registration number is already exist, please try with another number");
        return $this;
    }

    /**
     * @return $this
     */
    private function checkUsedTradeLicenseNumber()
    {
        if ($this->isNull($this->tradeLicenseNumber)) return $this;
        $partner_basic_info = PartnerBasicInformation::where('trade_license', $this->tradeLicenseNumber)->first();
        if ($partner_basic_info) $this->setError(409, "Trade license number is already exist, please try with another number");
        return $this;
    }

    /**
     * @param $data
     * @return bool
     */
    private function isNull($data)
    {
        if ($data == 'null') return true;
        if ($data == null) return true;
        if ($data == "") return true;
        return false;
    }
}