<?php namespace Sheba\Business\Vendor;

use Sheba\Repositories\ProfileRepository;

class CreateValidator
{
    /** @var CreateRequest $vehicleCreateRequest*/
    private $vendorCreateRequest;
    private $profileRepository;

    public function __construct(ProfileRepository $profile_repo)
    {
        $this->profileRepository = $profile_repo;
    }

    public function setVendorCreateRequest(CreateRequest $create_request)
    {
        $this->vendorCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        if ($this->vendorAlreadyAddWithBusiness())
            return ['code' => 421, 'msg' => 'Vendor Already Added!'];

        return false;
    }

    private function vendorAlreadyAddWithBusiness()
    {
        $resource_mobile = $this->vendorCreateRequest->getResourceMobile();
        $profile = $this->profileRepository->checkExistingMobile($resource_mobile);
        if ($profile && $profile->resource && $partner = $profile->resource->firstPartner()){
            return in_array($this->vendorCreateRequest->getBusiness()->id, $partner->businesses->pluck('id')->toArray());
        }

        return false;
    }
}