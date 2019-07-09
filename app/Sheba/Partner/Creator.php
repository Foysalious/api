<?php namespace Sheba\Partner;

use App\Models\Business;
use App\Models\Partner;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Repositories\PartnerBasicInformationRepository;
use Sheba\Repositories\PartnerRepository;
use DB;

class Creator
{
    use FileManager, CdnFileManager;

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
        $data = [
            'name' => $this->partnerCreateRequest->getName(),
            'sub_domain' => $this->partnerCreateRequest->getSubDomain(),
            'mobile' => $this->partnerCreateRequest->getMobile(),
            'email' => $this->partnerCreateRequest->getEmail(),
            'address' => $this->partnerCreateRequest->getAddress()
        ];

        if ($this->hasFile($this->partnerCreateRequest->getLogo())) {
            list($logo, $logo_original) = $this->saveLogoImage();
            $data += ['logo' => $logo, 'logo_original' => $logo_original];
        }

        return $data;
    }

    private function formatPartnerBasicSpecificData()
    {
        $data = [
            'partner_id' => $this->partner->id,
            'trade_license' => $this->partnerCreateRequest->getTradeLicense(),
            'vat_registration_number' => $this->partnerCreateRequest->getVatRegistrationNumber()
        ];
        if ($this->hasFile($this->partnerCreateRequest->getTradeLicenseAttachment()))
            $data += ['trade_license_attachment' => $this->saveTradeLicenseAttachment()];

        if ($this->hasFile($this->partnerCreateRequest->getVatRegistrationDocument()))
            $data += ['vat_registration_attachment' => $this->saveVatRegistrationDocument()];

        return $data;
    }

    private function saveLogoImage()
    {
        list($avatar, $avatar_filename) = $this->makeThumb($this->partnerCreateRequest->getLogo(), $this->partnerCreateRequest->getName());
        $logo_url = $this->saveImageToCDN($avatar, getPartnerLogoFolder(), $avatar_filename);
        $logo_original_url = $this->saveImageToCDN($avatar, getPartnerLogoFolder(), 'original_' . $avatar_filename);
        return [$logo_url, $logo_original_url];
    }

    private function hasFile($file)
    {
        return ($file instanceof Image || ($file instanceof UploadedFile && $file->getPath() != ''));
    }

    private function saveTradeLicenseAttachment()
    {
        list($avatar, $avatar_filename) = $this->makeTradeLicense($this->partnerCreateRequest->getTradeLicenseAttachment(), $this->partnerCreateRequest->getName());
        return $this->saveImageToCDN($avatar, getTradeLicenceImagesFolder(), $avatar_filename);
    }

    private function saveVatRegistrationDocument()
    {
        list($avatar, $avatar_filename) = $this->makeVatRegistration($this->partnerCreateRequest->getVatRegistrationDocument(), $this->partnerCreateRequest->getName());
        return $this->saveImageToCDN($avatar, getVatRegistrationImagesFolder(), $avatar_filename);
    }
}