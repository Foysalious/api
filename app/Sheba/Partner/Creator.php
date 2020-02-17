<?php namespace Sheba\Partner;

use App\Models\Partner;
use App\Models\PartnerWalletSetting;
use App\Sheba\Repositories\PartnerWalletSettingRepository;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;
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
    /** @var PartnerWalletSettingRepository $partnerWalletSettingRepository */
    private $partnerWalletSettingRepository;

    public function __construct(PartnerRepositoryInterface $partner_repository, PartnerBasicInformationRepository $basic_information_repository, PartnerWalletSettingRepository $wallet_setting_repository)
    {
        $this->partnerRepository = $partner_repository;
        $this->partnerBasicInformationRepository = $basic_information_repository;
        $this->partnerWalletSettingRepository = $wallet_setting_repository;
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
            $this->partnerWalletSettingRepository->create($this->partnerWalletSettingsDefault());
        });
        return $this->partner;
    }

    private function partnerWalletSettingsDefault()
    {
        $data = [
            'partner_id' => $this->partner->id,
            'security_money' => constants('PARTNER_DEFAULT_SECURITY_MONEY')
        ];
        return $data;
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

        if ($this->hasFile($this->partnerCreateRequest->getTradeLicenseAttachment())) {
            $data += ['trade_license_attachment' => $this->saveTradeLicenseAttachment()];
        }

        if ($this->hasFile($this->partnerCreateRequest->getVatRegistrationDocument())) {
            $data += ['vat_registration_attachment' => $this->saveVatRegistrationDocument()];
        }

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
        return ($this->hasImage($file) || ($file instanceof UploadedFile && $file->getPath() != ''));
    }

    private function hasImage($file)
    {
        return ($file instanceof Image);
    }

    private function saveTradeLicenseAttachment()
    {
        $file = $this->partnerCreateRequest->getTradeLicenseAttachment();
        $file_name = $this->partnerCreateRequest->getName();
        if ($this->hasImage($file)) {
            list($avatar, $avatar_filename) = $this->makeTradeLicense($file, $file_name);
            return $this->saveImageToCDN($avatar, getTradeLicenceImagesFolder(), $avatar_filename);
        } else {
            list($avatar, $avatar_filename) = $this->makeTradeLicenseDocument($file, $file_name);
            return $this->saveFileToCDN($avatar, getTradeLicenceDocumentsFolder(), $avatar_filename);
        }
    }

    private function saveVatRegistrationDocument()
    {
        $file = $this->partnerCreateRequest->getVatRegistrationDocument();
        $file_name = $this->partnerCreateRequest->getName();
        if ($file) {
            list($avatar, $avatar_filename) = $this->makeVatRegistration($file, $file_name);
            return $this->saveImageToCDN($avatar, getVatRegistrationImagesFolder(), $avatar_filename);
        } else {
            list($avatar, $avatar_filename) = $this->makeVatRegistrationDocument($file, $file_name);
            return $this->saveFileToCDN($avatar, getVatRegistrationDocumentsFolder(), $avatar_filename);
        }
    }
}