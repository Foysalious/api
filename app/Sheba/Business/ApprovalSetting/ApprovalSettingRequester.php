<?php namespace Sheba\Business\ApprovalSetting;


class ApprovalSettingRequester
{
    private $modules;
    private $targetType;
    private $targetId;
    private $note;
    private $approvers;

    public function setModules($modules)
    {
        $this->modules = $modules;
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
}
