<?php namespace Sheba\Business\ApprovalSetting;


use App\Models\Business;
use Sheba\Business\ApprovalSettingModule\ModuleRequester;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\ModificationFields;
use Sheba\Business\ApprovalSettingModule\Creator as ApprovalSettingModuleCreator;

class Creator
{
     use ModificationFields;
    /**
     * @var ApprovalSettingRepository
     */
    private $approvalSettingRepo;
    /**
     * @var ApprovalSettingRequester
     */
    private $approvalSettingRequester;

    private $approvalSettingData=[];
    /**
     * @var Business
     */
    private $business;
    /**
     * @var ApprovalSettingModuleCreator
     */
    private $approvalSettingModuleCreator;
    /**
     * @var ModuleRequester
     */
    private $moduleRequester;

    public function __construct(ApprovalSettingRepository $approval_setting_repo,
                                ModuleRequester $module_requester,
                                ApprovalSettingModuleCreator $approval_setting_module_creator)
    {
        $this->approvalSettingRepo = $approval_setting_repo;
        $this->moduleRequester = $module_requester;
        $this->approvalSettingModuleCreator = $approval_setting_module_creator;
    }

    public function setApprovalSettingRequester(ApprovalSettingRequester $approval_setting_requester)
    {
        $this->approvalSettingRequester = $approval_setting_requester;
        return $this;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
    }

    public function create()
    {
        $approval_setting = $this->approvalSettingRepo->create($this->withCreateModificationField($this->approvalSettingData));
        $this->moduleRequester->setModules($this->approvalSettingRequester->getModules());
        $this->approvalSettingModuleCreator->setModuleRequester($this->moduleRequester)->setApprovalSetting($approval_setting)->create();
        return $this;
    }

    private function makeData()
    {
        $this->approvalSettingData = [
            'business_id' => $this->business->id,
            'target_type' => $this->approvalSettingRequester->getTargetType(),
            'target_id' => $this->approvalSettingRequester->getTargetId(),
            'note' => $this->approvalSettingRequester->getNote(),
        ];
    }
}