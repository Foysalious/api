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
        $this->checkValidModule();
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

    private function checkValidModule()
    {
        if (!$this->modules) {
            foreach ($this->modules as $module) {
                if (!in_array($module, Modules::get())) array_push($this->validModules, $module);
            }
            if (count($this->validModules) > 0) $this->setError(420, (implode(', ', $this->validModules)) . ' is not valid module.');
        }
    }

    public function checkValidation()
    {
        if ($this->targetType == Targets::GENERAL_MODULE) $this->setError(420, 'This approval flow is already present in this system. Please select different options to add new flow.');
        
        $approval_settings = $this->approvalSettingsRepo->where('business_id', $this->business->id)
            ->where('target_type', $this->targetType);
        if ($this->targetId) $approval_settings = $approval_settings->where('target_id', $this->targetId);

        $approval_settings = $approval_settings->whereHas('modules', function ($module_query) {
            $module_query->whereIn('modules', $this->modules);
        })->get();

        if (!$approval_settings->isEmpty()) $this->setError(420, 'This approval flow is already present in this system. Please select different options to add new flow.');
        return $this;
    }
}
