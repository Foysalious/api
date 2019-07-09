<?php namespace Sheba\Business\Vehicle;

use App\Models\VehicleRegistrationInformation;
use Sheba\Repositories\ProfileRepository;

class CreateValidator
{
    /** @var CreateRequest $vehicleCreateRequest*/
    private $vehicleCreateRequest;
    private $profileRepository;

    public function __construct(ProfileRepository $profile_repo)
    {
        $this->profileRepository = $profile_repo;
    }

    public function setVehicleCreateRequest(CreateRequest $create_request)
    {
        $this->vehicleCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        if (!$this->isVendorExist()) return ['code' => 421, 'msg' => 'Vendor not exits!'];
        if ($this->isLicenseNumberExist()) return ['code' => 421, 'msg' => 'License number already exist!'];
        if ($this->isTaxTokenNumberExist()) return ['code' => 421, 'msg' => 'Tax Token number already exist!'];
    }

    private function isVendorExist()
    {
        $resource_mobile = $this->vehicleCreateRequest->getVendorPhoneNumber();
        if (!$resource_mobile) return true;
        if ($resource_mobile) {
            $profile = $this->profileRepository->checkExistingMobile($resource_mobile);
            if ($profile && $profile->resource && $profile->resource->firstPartner())
                return true;
        }

        return false;
    }

    private function isLicenseNumberExist()
    {
        return VehicleRegistrationInformation::where('license_number', $this->vehicleCreateRequest->getLicenseNumber())->first();
    }

    private function isTaxTokenNumberExist()
    {
        return VehicleRegistrationInformation::where('tax_token_number', $this->vehicleCreateRequest->getTaxTokenNumber())->first();
    }
}