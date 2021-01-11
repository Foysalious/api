<?php namespace Sheba\Business\ApprovalSetting;

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

    public function setModules($modules)
    {
        $this->modules = $modules;
        if ($this->modules) $this->modules = json_decode($this->modules, 1);
        $this->moduleValidation();
        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function setTargetType($target_type)
    {
        $this->targetType = $target_type;
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

    /**
     * @return $this
     */
    private function moduleValidation()
    {
        $modules = [];
        foreach ($this->modules as $module) {
            if (!in_array($module, Modules::get())) array_push($modules, $module);
        }
        if (count($modules) > 0) $this->setError(420, (implode(', ', $modules)) . ' is not valid module');
        return $this;
    }
}
