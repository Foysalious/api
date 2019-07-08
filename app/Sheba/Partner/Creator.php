<?php namespace Sheba\Partner;

use App\Models\Business;
use App\Models\Partner;
use Sheba\Repositories\PartnerBasicInformationRepository;
use Sheba\Repositories\PartnerRepository;
use DB;

class Creator
{
    /** @var CreateRequest $partnerCreateRequest */
    private $partnerCreateRequest;
    /** @var PartnerRepository $partnerRepository */
    private $partnerRepository;
    /** @var PartnerBasicInformationRepository $partnerBasicInformationRepository */
    private $partnerBasicInformationRepository;
    /** @var Partner $partner */
    private $partner;

    public function __construct(PartnerRepository $partner_repository, PartnerBasicInformationRepository $basic_information_repository)
    {
        $this->partnerRepository = $partner_repository;
        $this->partnerBasicInformationRepository = $basic_information_repository;
    }

    public function setPartnerCreateRequest(CreateRequest $create_request)
    {
        $this->partnerCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
    }

    public function create()
    {
        DB::transaction(function () {
            $this->partner = $this->partnerRepository->create($this->formatPartnerGeneralSpecificData());
            $this->partnerBasicInformationRepository->create($this->formatPartnerBasicSpecificData());
        });

        return $this->partner;
    }

    private function formatPartnerGeneralSpecificData()
    {
        return [
            'name' => $this->partnerCreateRequest->getName(),
            'sub_domain' => $this->partnerCreateRequest->getSubDomain(),
            'mobile' => $this->partnerCreateRequest->getMobile(),
            'email' => $this->partnerCreateRequest->getEmail(),
            'address' => $this->partnerCreateRequest->getAddress()
        ];
    }

    private function formatPartnerBasicSpecificData()
    {
        return [
            'partner_id' => $this->partner->id,
            'trade_license' => $this->partnerCreateRequest->getTradeLicense(),
            'vat_registration_number' => $this->partnerCreateRequest->getVatRegistrationNumber()
        ];
    }

    private function formatBusinessPartnerSpecificData()
    {
        return [
            ''
        ];
    }
}