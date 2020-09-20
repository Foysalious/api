<?php namespace Sheba\Business\Vendor;


use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;

class Updater
{
    /** @var UpdateRequest $vendorUpdateRequest */
    private $vendorUpdateRequest;
    private $partnerRepository;

    public function __construct(PartnerRepositoryInterface $partner_repository)
    {
        $this->partnerRepository = $partner_repository;
    }

    /**
     * @param UpdateRequest $update_request
     * @return $this
     */
    public function setVendorUpdateRequest(UpdateRequest $update_request)
    {
        $this->vendorUpdateRequest = $update_request;
        return $this;
    }

    public function activeInactiveForB2b()
    {
        $this->partnerRepository->update($this->vendorUpdateRequest->getVendor(), ['is_active_for_b2b' => $this->vendorUpdateRequest->getIsActiveForB2B()]);
    }
}