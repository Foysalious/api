<?php namespace Sheba\Business\ApprovalSettingModule;


use Sheba\Dal\ApprovalSetting\ApprovalSetting;

class Creator
{
    /**
     * @var ApprovalSetting
     */
    private $approvalSetting;

    public function setApprovalSetting(ApprovalSetting $approval_setting)
    {
        $this->approvalSetting = $approval_setting;
    }

}