<?php namespace Sheba\Business\ApprovalSettingModule;


use Sheba\Dal\ApprovalSetting\ApprovalSetting;

class ModuleRequester
{
    private $modules;

    public function setModules($modules)
    {
        $this->modules = $modules;
        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

}