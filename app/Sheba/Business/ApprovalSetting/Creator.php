<?php namespace Sheba\Business\ApprovalSetting;

use Sheba\Business\ApprovalSettingModule\Creator as ApprovalSettingModuleCreator;
use  Sheba\Business\ApprovalSettingApprover\Creator as ApprovalSettingApproverCreator;
use Sheba\Business\ApprovalSettingApprover\ApproverRequester;
use Sheba\Business\ApprovalSettingModule\ModuleRequester;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\ModificationFields;
use App\Models\Business;

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

    private $approvalSettingData = [];
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
    /**
     * @var ApproverRequester
     */
    private $approverRequester;
    /**
     * @var ApprovalSettingApproverCreator
     */
    private $approverSettingApproverCreator;
    private $isDefault;

    /**
     * Creator constructor.
     * @param ApprovalSettingRepository $approval_setting_repo
     * @param ModuleRequester $module_requester
     * @param ApprovalSettingModuleCreator $approval_setting_module_creator
     * @param ApproverRequester $approver_requester
     * @param ApprovalSettingApproverCreator $approval_setting_approver_creator
     */
    public function __construct(ApprovalSettingRepository $approval_setting_repo,
                                ModuleRequester $module_requester,
                                ApprovalSettingModuleCreator $approval_setting_module_creator,
                                ApproverRequester $approver_requester,
                                ApprovalSettingApproverCreator $approval_setting_approver_creator)
    {
        $this->approvalSettingRepo = $approval_setting_repo;
        $this->moduleRequester = $module_requester;
        $this->approvalSettingModuleCreator = $approval_setting_module_creator;
        $this->approverRequester = $approver_requester;
        $this->approverSettingApproverCreator = $approval_setting_approver_creator;
    }

    /**
     * @param ApprovalSettingRequester $approval_setting_requester
     * @return $this
     */
    public function setApprovalSettingRequester(ApprovalSettingRequester $approval_setting_requester)
    {
        $this->approvalSettingRequester = $approval_setting_requester;
        return $this;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        $approval_setting = null;
        DB::transaction(function () use ($approval_setting) {
            $approval_setting = $this->approvalSettingRepo->create($this->approvalSettingData);
            $this->createApprovalSettingModules($approval_setting);
            $this->createApprovalSettingApprover($approval_setting);
        });
        return $approval_setting;
    }

    private function makeData()
    {
        $this->approvalSettingData = [
            'business_id' => $this->business->id,
            'target_type' => $this->approvalSettingRequester->getIsDefault() ? Targets::GENERAL : $this->approvalSettingRequester->getTargetType(),
            'target_id' => $this->approvalSettingRequester->getTargetId(),
            'note' => $this->approvalSettingRequester->getNote(),
        ];
    }

    /**
     * @param ApprovalSetting $approval_setting
     */
    private function createApprovalSettingModules(ApprovalSetting $approval_setting)
    {
        $this->moduleRequester->setModules($this->approvalSettingRequester->getModules());
        $this->approvalSettingModuleCreator->setModuleRequester($this->moduleRequester)->setApprovalSetting($approval_setting)->create();
    }

    /**
     * @param ApprovalSetting $approval_setting
     */
    private function createApprovalSettingApprover(ApprovalSetting $approval_setting)
    {
        $this->approverRequester->setApprovers($this->approvalSettingRequester->getApprovers());
        $this->approverSettingApproverCreator->setApproverRequester($this->approverRequester)->setApprovalSetting($approval_setting)->create();
    }
}