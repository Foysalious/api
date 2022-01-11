<?php namespace Sheba\Business\ApprovalSettingApprover;


class ApproverRequester
{
    private $approvers;

    public function setApprovers($approvers)
    {
        $this->approvers = $approvers;
    }

    public function getApprovers()
    {
        return $this->approvers;
    }
}