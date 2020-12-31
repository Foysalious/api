<?php namespace Sheba\Business\ApprovalSetting;


use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Sheba\Business\ApprovalSettingApprover\ApproverRequester;
use Sheba\Business\ApprovalSettingModule\ModuleRequester;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Business\ApprovalSettingModule\Updater as ApprovalSettingModuleUpdater;
use Sheba\Business\ApprovalSettingApprover\Updater as ApprovalSettingApproverUpdater;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
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
     * @var ApproverRequester
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

    public function setApprovalSettingRequester(ApprovalSettingRequester $approval_setting_requester)
    {
        $this->approvalSettingRequester = $approval_setting_requester;
        return $this;
    }

    public function setApprovalSettings($approval_settings)
    {
        $this->approvalSettings = $approval_settings;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        $approval_setting = null;
        DB::transaction(function () use ($approval_setting) {
            $this->approvalSettingRepo->update($this->approvalSettings, $this->approvalSettingData);
            $this->updateApprovalSettingModules();
            $this->updateApprovalSettingApprover();
        });
    }

    public function makeData()
    {
        $this->approvalSettingData = [
            'target_type' => $this->approvalSettingRequester->getTargetType(),
            'target_id' => $this->approvalSettingRequester->getTargetId(),
            'note' => $this->approvalSettingRequester->getNote(),
        ];
    }

    private function updateApprovalSettingModules()
    {
        $new_modules = $this->approvalSettingRequester->getModules();
        $modules = $this->approvalSettings->modules;

        $updated_modules = [];
        foreach ($modules as $module)
        {
            if (!in_array($module->modules, $new_modules))
            {
                $this->approvalSettingModuleRepo->delete($module);
            }
            $updated_modules[$module->modules] = $module->modules;
        }
        foreach ($new_modules as $module)
        {
            if (!in_array($module, $updated_modules))
            {
                $this->approvalSettingModuleData[] = [
                    'approval_setting_id' => $this->approvalSettings->id,
                    'modules' => $module,
                ];
            }
        }
        $this->approvalSettingModuleRepo->insert($this->approvalSettingModuleData);
    }

    private function updateApprovalSettingApprover()
    {
        $new_approvars = $this->approvalSettingRequester->getApprovers();
        $approvar_type = [];
        foreach ($new_approvars as $new_approvar)
        {
            $approvar_type[] = $new_approvar['type'];
        }
        $approvars = $this->approvalSettings->approvers;
        $updated_approvars = [];
        foreach ($approvars as $approvar)
        {
            if (!in_array($approvar->type, $approvar_type))
            {
                $this->approvalSettingModuleRepo->delete($approvar);
            }else{
                $index = array_search($approvar->type, array_column($new_approvars, 'type'));
                $this->approvalSettingModuleRepo->update($approvar, $new_approvars[$index]);
            }
            $updated_approvars[$approvar->type] = $approvar->type;
        }

        foreach ($new_approvars as $new_approvar)
        {
            if (!in_array($new_approvar['type'], $updated_approvars))
            {
                $this->approvalSettingApproverData[] = [
                    'approval_setting_id' => $this->approvalSettings->id,
                    'type' => $new_approvar['type'],
                    'type_id' => $new_approvar['type_id'],
                    'order' => $new_approvar['order'],
                ];
            }
        }
        $this->approvalSettingApproverRepo->insert($this->approvalSettingApproverData);
    }
}
