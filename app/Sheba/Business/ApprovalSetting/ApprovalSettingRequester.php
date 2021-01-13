<?php namespace Sheba\Business\ApprovalSetting;

use App\Models\Business;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\Dal\ApprovalSettingModule\Modules;
use Sheba\Helpers\HasErrorCodeAndMessage;

class ApprovalSettingRequester
{
    use HasErrorCodeAndMessage;

    private $modules;
    private $targetType;
    private $targetId;
    private $note;
    private $approvers;
    /**
     * @var ApprovalSettingRepository
     */
    private $approvalSettingsRepo;
    /**
     * @var Business $business
     */
    private $business;
    private $isDefault;
    private $validModules;
    private $usedModules;

    public function __construct()
    {
        $this->approvalSettingsRepo = app(ApprovalSettingRepository::class);
        $this->validModules = [];
        $this->usedModules = [];
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setIsDefault($default)
    {
        $this->isDefault = $default;
        return $this;
    }

    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function setTargetType($target_type)
    {
        $this->targetType = $target_type;
        if ($this->isDefault) {
            $this->targetType = Targets::GENERAL;
        } elseif ($this->targetType == Targets::GENERAL) {
            $this->targetType = Targets::GENERAL_MODULE;
        }
        return $this;
    }

    public function getTargetType()
    {
        return $this->targetType;
    }

    public function setTargetId($target_id)
    {
        $this->targetId = $target_id;
        return $this;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    public function setModules($modules)
    {
        $this->modules = $modules;
        if ($this->modules) $this->modules = json_decode($this->modules, 1);
        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setApprovers($approvers)
    {
        $this->approvers = $approvers;
        if ($this->approvers) $this->approvers = json_decode($this->approvers, 1);
        return $this;
    }

    public function getApprovers()
    {
        return $this->approvers;
    }

    public function checkValidation()
    {
        foreach ($this->modules as $module) {
            if (!in_array($module, Modules::get())) array_push($this->validModules, $module);
        }
        if (count($this->validModules) > 0) $this->setError(420, (implode(', ', $this->validModules)) . ' is not valid module.');

        if ($this->targetType && $this->targetId) {
            $approval_settings = $this->approvalSettingsRepo->where('business_id', $this->business->id)->with('modules')
                ->where('target_type', $this->targetType)->where('target_id', $this->targetId)->get();
            $this->areThoseModuleUsed($approval_settings);
        }

        if ($this->targetType) {
            $approval_settings = $this->approvalSettingsRepo->where('business_id', $this->business->id)->with('modules')
                ->where('target_type', $this->targetType)->get();
            $this->areThoseModuleUsed($approval_settings);
        }
        if (count($this->usedModules) > 0) $this->setError(420, 'This approval flow is already present in this system. Please select different options to add new flow.');
        return $this;
    }

    public function areThoseModuleUsed($approval_settings)
    {
        foreach ($approval_settings as $approval_setting) {
            foreach ($this->modules as $module) {
                $approval_setting_module = $approval_setting->modules()->where('modules', $module)->first();
                if ($approval_setting_module) array_push($this->usedModules, $module);
            }
        }
        return $this->usedModules;
    }
}
