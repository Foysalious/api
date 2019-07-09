<?php namespace Sheba\Business\Driver;

use Carbon\Carbon;
use Sheba\Repositories\ProfileRepository;

class CreateValidator
{
    /** @var CreateRequest $driverCreateRequest*/
    private $driverCreateRequest;
    private $profileRepository;

    public function __construct(ProfileRepository $profile_repo)
    {
        $this->profileRepository = $profile_repo;
    }

    public function setDriverCreateRequest(CreateRequest $create_request)
    {
        $this->driverCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        if ($this->isDriverAlreadyExist()) return ['code' => 421, 'msg' => 'Driver already exits!'];
        if (!$this->isVendorExist()) return ['code' => 421, 'msg' => 'Vendor not exits!'];
        if ($this->isDateOfBirthInvalid()) return ['code' => 421, 'msg' => 'Birth Date is invalid!'];
    }

    private function isDateOfBirthInvalid()
    {
        if ($this->driverCreateRequest->getDateOfBirth()) {
            return !$this->driverCreateRequest->getDateOfBirth()->isPast();
        }

        return false;
    }

    private function isDriverAlreadyExist()
    {
        $profile = $this->profileRepository->checkExistingMobile($this->driverCreateRequest->getMobile());
        if ($profile && $profile->driver) return true;
        return false;
    }

    private function isVendorExist()
    {
        $resource_mobile = $this->driverCreateRequest->getVendorMobile();
        $profile = $this->profileRepository->checkExistingMobile($resource_mobile);
        if ($profile && $profile->resource && $profile->resource->firstPartner())
            return true;

        return false;
    }
}