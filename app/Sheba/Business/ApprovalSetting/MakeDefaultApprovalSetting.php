<?php namespace Sheba\Business\ApprovalSetting;


use Sheba\Dal\ApprovalSettingModule\Modules;

class MakeDefaultApprovalSetting
{

    public function getApprovalSettings()
    {
        return [
            'id' => null,
            'business_id' => null,
            'is_default' => 1,
            'note' => 'Default Approval Setting',
            'target_type' => $this->getTargetType(),
            'modules' => $this->getModule(),
            'is_all_modules' => $this->isAllModules(),
            'approvers' => $this->getApprovers(),
            'approver_count' => count($this->getApprovers()),
        ];
    }

    private function isAllModules()
    {
        if (count(Modules::get()) == count($this->getModule())) return 1;
        return 0;
    }

    private function getTargetType()
    {
        return [
            "id" => null,
            "type" => "Global",
            "employee" => null,
            "department" => null
        ];
    }

    private function getModule()
    {
        return [
            [
                "id" => null,
                "approval_setting_id" => null,
                "name" => "Leave"
            ],
 /*           [
                "id" => null,
                "approval_setting_id" => null,
                "name" => "Support"
            ],
            [
                "id" => null,
                "approval_setting_id" => null,
                "name" => "Expense"
            ]*/
        ];
    }

    private function getApprovers()
    {
        return [
            [
                "id" => null,
                "type" => "lm",
                "type_id" => null,
                "name" => null,
                "employee_id" => null,
                "department" => null,
                "profile_pic" => null,
                'order' => 1
            ],
            [
                "id" => null,
                "type" => "hod",
                "type_id" => null,
                "name" => null,
                "employee_id" => null,
                "department" => null,
                "profile_pic" => null,
                'order' => 2
            ]];
    }
}