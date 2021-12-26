<?php namespace Sheba\Business\ApprovalSettingApprover;

use Sheba\Dal\ApprovalSettingApprover\ApprovalSettingApproverRepository;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;

class Updater
{
    /**
     * @var ApprovalSetting
     */
    private $approvalSetting;
    /**
     * @var ApproverRequester
     */
    private $approverRequester;
    private $approvalSettingApprover = [];
    /**
     * @var ApprovalSettingApproverRepository
     */
    private $approvalSettingApproverRepo;

    public function __construct(ApprovalSettingApproverRepository $approval_setting_approver_repo)
    {
        $this->approvalSettingApproverRepo = $approval_setting_approver_repo;
    }

    public function setApproverRequester(ApproverRequester $approver_requester)
    {
        $this->approverRequester = $approver_requester;
        return $this;
    }

    public function setApprovalSetting(ApprovalSetting $approval_setting)
    {
        $this->approvalSetting = $approval_setting;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        $this->approvalSettingApproverRepo->insert($this->approvalSettingApprover);
        return $this;
    }

    private function makeData()
    {
        foreach ($this->approverRequester->getApprovers() as $approver) {
            $this->approvalSettingApprover[] = [
                'approval_setting_id' => $this->approvalSetting->id,
                'type' => $approver['type'],
                'type_id' => $approver['type_id'],
                'order' => $approver['order']
            ];
        }
    }
}
