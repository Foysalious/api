<?php namespace Sheba\Business\ApprovalSetting;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Sheba\Business\ApprovalSettingApprover\ApproverRequester;
use Sheba\Business\ApprovalSettingModule\ModuleRequester;
use Sheba\Business\ApprovalSettingModule\Updater as ApprovalSettingModuleUpdater;
use Sheba\Business\ApprovalSettingApprover\Updater as ApprovalSettingApproverUpdater;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\ModificationFields;
use Sheba\Dal\ApprovalSettingModule\ApprovalSettingModuleRepository;
use Sheba\Dal\ApprovalSettingApprover\ApprovalSettingApproverRepository;

class Updater
{
    use ModificationFields;

    private $approvalSettingData = [];
    /**
     * @var ApprovalSettingRepository
     */
    private $approvalSettingRepo;

    /**
     * @var ApprovalSettingRequester
     */
    private $approvalSettingRequester;
    /**
     * @var Business
     */
    private $business;
    private $approvalSettings;
    /**
     * @var ModuleRequester
     */
    private $moduleRequester;
    /**
     * @var ApproverRequester
     */
    private $approverRequester;
    /**
     * @var ApprovalSettingModuleUpdater
     */
    private $approvalSettingModuleUpdater;
    /**
     * @var ApprovalSettingApproverUpdater
     */
    private $approverSettingApproverUpdater;
    /*** @var ApprovalSettingModuleRepository $approval_setting_module_repo */
    private $approvalSettingModuleRepo;
    private $approvalSettingModuleData = [];
    private $approvalSettingApproverData = [];
    private $approvalSettingApproverRepo;
    private $isDefault;

    /**
     * Updater constructor.
     * @param ApprovalSettingRepository $approval_setting_repo
     * @param ModuleRequester $module_requester
     * @param ApproverRequester $approver_requester
     * @param ApprovalSettingModuleUpdater $approval_setting_module_updater
     * @param ApprovalSettingApproverUpdater $approval_setting_approver_updater
     * @param ApprovalSettingModuleRepository $approval_setting_module_repo
     * @param ApprovalSettingApproverRepository $approval_setting_approver_repo
     */
    public function __construct(ApprovalSettingRepository $approval_setting_repo,
                                ModuleRequester $module_requester,
                                ApproverRequester $approver_requester,
                                ApprovalSettingModuleUpdater $approval_setting_module_updater,
                                ApprovalSettingApproverUpdater $approval_setting_approver_updater,
                                ApprovalSettingModuleRepository $approval_setting_module_repo,
                                ApprovalSettingApproverRepository $approval_setting_approver_repo)
    {
        $this->approvalSettingRepo = $approval_setting_repo;
        $this->moduleRequester = $module_requester;
        $this->approverRequester = $approver_requester;
        $this->approvalSettingModuleUpdater = $approval_setting_module_updater;
        $this->approverSettingApproverUpdater = $approval_setting_approver_updater;
        $this->approvalSettingModuleRepo = $approval_setting_module_repo;
        $this->approvalSettingApproverRepo = $approval_setting_approver_repo;
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
     * @param $approval_settings
     * @return $this
     */
    public function setApprovalSettings($approval_settings)
    {
        $this->approvalSettings = $approval_settings;
        return $this;
    }

    /**
     * @param $is_default
     * @return $this
     */
    public function setIsDefault($is_default)
    {
        $this->isDefault = $is_default;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        DB::transaction(function () {
            $this->approvalSettingRepo->update($this->approvalSettings, $this->approvalSettingData);
            $this->updateApprovalSettingModules();
            $this->updateApprovalSettingApprover();
        });
    }

    public function makeData()
    {
        if ($this->approvalSettingRequester->getTargetType()) {
            $this->approvalSettingData['target_type'] = $this->approvalSettingRequester->getIsDefault() ? Targets::GENERAL : $this->approvalSettingRequester->getTargetType();
        }
        if ($this->approvalSettingRequester->getTargetId()) {
            $this->approvalSettingData['target_id'] = $this->approvalSettingRequester->getTargetId();
        }
        if ($this->approvalSettingRequester->getNote()) {
            $this->approvalSettingData['note'] = $this->approvalSettingRequester->getNote();
        }
    }

    private function updateApprovalSettingModules()
    {
        $this->approvalSettings->modules()->delete();
        $this->moduleRequester->setModules($this->approvalSettingRequester->getModules());
        $this->approvalSettingModuleUpdater->setModuleRequester($this->moduleRequester)->setApprovalSetting($this->approvalSettings)->update();
    }

    private function updateApprovalSettingApprover()
    {
        $this->approvalSettings->approvers()->delete();
        $this->approverRequester->setApprovers($this->approvalSettingRequester->getApprovers());
        $this->approverSettingApproverUpdater->setApproverRequester($this->approverRequester)->setApprovalSetting($this->approvalSettings)->update();
    }
}
