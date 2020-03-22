<?php namespace Sheba\Business\Vendor;

use App\Models\Category;
use App\Models\Profile;
use Sheba\ModificationFields;
use DB;
use Sheba\Partner\CreateRequest as PartnerCreateRequest;
use Sheba\Repositories\ProfileRepository;
use Sheba\Resource\ResourceCreator;
use Sheba\Partner\Creator as PartnerCreator;

class Creator
{
    use ModificationFields;

    /** @var CreateRequest $vendorCreateRequest */
    private $vendorCreateRequest;
    /** @var CreateValidator $validator */
    private $validator;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var ResourceCreator $resourceCreator */
    private $resourceCreator;
    /** @var PartnerCreator $partnerCreator */
    private $partnerCreator;
    /** @var PartnerCreateRequest $partnerCreateRequest */
    private $partnerCreateRequest;
    /** @var $partner */
    private $partner;

    /**
     * Creator constructor.
     * @param CreateValidator $validator
     * @param ProfileRepository $profile_repo
     * @param ResourceCreator $resource_creator
     * @param PartnerCreator $partner_creator
     * @param PartnerCreateRequest $partner_create_request
     */
    public function __construct(CreateValidator $validator, ProfileRepository $profile_repo,
                                ResourceCreator $resource_creator, PartnerCreator $partner_creator,
                                PartnerCreateRequest $partner_create_request)
    {
        $this->validator = $validator;
        $this->profileRepository = $profile_repo;
        $this->resourceCreator = $resource_creator;
        $this->resourceCreator = $resource_creator;
        $this->partnerCreator = $partner_creator;
        $this->partnerCreateRequest = $partner_create_request;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setVendorCreateRequest(CreateRequest $create_request)
    {
        $this->vendorCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        $this->validator->setVendorCreateRequest($this->vendorCreateRequest);
        return $this->validator->hasError();
    }

    public function create()
    {
        DB::transaction(function () {
            $resource_mobile = $this->vendorCreateRequest->getResourceMobile();
            /** @var Profile $profile */
            $profile = $this->profileRepository->checkExistingMobile($resource_mobile);
            if (!$profile) {
                $this->resourceCreator->setData($this->formatProfileSpecificData());
                $resource = $this->resourceCreator->create();
            } elseif (!$profile->resource) {
                $this->resourceCreator->setData($this->formatProfileSpecificData());
                $resource = $this->resourceCreator->create();
            } else {
                $resource = $profile->resource;
            }

            $request = $this->partnerCreateRequest
                ->setName($this->vendorCreateRequest->getVendorName())
                ->setLogo($this->vendorCreateRequest->getVendorImage())
                ->setMobile($this->vendorCreateRequest->getVendorMobile())
                ->setEmail($this->vendorCreateRequest->getVendorEmail())
                ->setAddress($this->vendorCreateRequest->getVendorAddress())
                ->setTradeLicense($this->vendorCreateRequest->getTradeLicenseNumber())
                ->setTradeLicenseAttachment($this->vendorCreateRequest->getTradeLicenseDocument())
                ->setVatRegistrationNumber($this->vendorCreateRequest->getVatRegistrationNumber())
                ->setVatRegistrationDocument($this->vendorCreateRequest->getVatRegistrationDocument());

            if (!$resource->firstPartner()) {
                $this->partner = $this->partnerCreator->setPartnerCreateRequest($request)->create();
                $this->partner->subscribe(config('sheba.partner_lite_packages_id'), 'monthly');

                $this->partner->resources()->save($resource, ['resource_type' => 'Admin']);
            } else {
                $this->partner = $resource->firstPartner();
            }

            if ($master_category = $this->vendorCreateRequest->getVendorMasterCategories()) {
                $secondary_categories = Category::find($master_category)->children()->pluck('id')->toArray();
                $this->partner->categories()->attach($secondary_categories);
            }

            if (!$this->alreadyAddSameBusiness()) $this->partner->businesses()->save($this->vendorCreateRequest->getBusiness());
        });

        return $this->partner;
    }

    private function formatProfileSpecificData()
    {
        return [
            'name' => $this->vendorCreateRequest->getResourceName(),
            'mobile' => $this->vendorCreateRequest->getResourceMobile(),
            'nid_no' => $this->vendorCreateRequest->getResourceNidNumber() ?: null,
            'alternate_contact' => null,
            'nid_image' => $this->vendorCreateRequest->getResourceNidDocument()
        ];
    }

    private function alreadyAddSameBusiness()
    {
        return in_array($this->vendorCreateRequest->getBusiness()->id, $this->partner->businesses->pluck('id')->toArray());
    }
}