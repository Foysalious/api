<?php namespace Sheba\Business\ApprovalSetting;


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
            'approvers' => $this->getApprovers(),
            'approver_count' => count($this->getApprovers()),
        ];
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
            [
                "id" => null,
                "approval_setting_id" => null,
                "name" => "Support"
            ],
            [
                "id" => null,
                "approval_setting_id" => null,
                "name" => "Expense"
            ]
        ];
    }

    private function getApprovers()
    {
        return [
            [
                "id" => null,
                "type" => "Lm",
                "type_id" => null,
                "name" => null,
                "employee_id" => null,
                "department" => null,
                "profile_pic" => null
            ],
            [
                "id" => null,
                "type" => "Hod",
                "type_id" => null,
                "name" => null,
                "employee_id" => null,
                "department" => null,
                "profile_pic" => null
            ]];
    }
}