<?php namespace Sheba\Business\Vehicle;

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
}