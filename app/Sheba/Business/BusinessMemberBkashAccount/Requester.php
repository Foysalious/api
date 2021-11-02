<?php namespace App\Sheba\Business\BusinessMemberBkashAccount;

use App\Sheba\Business\BusinessMemberBkashAccount\Creator as CoWorkerBkashAccountCreator;
use App\Sheba\Business\BusinessMemberBkashAccount\Updater as CoWorkerBkashAccountUpdater;
use App\Models\BusinessMember;

class Requester
{

    private $business;
    private $businessMember;
    private $member;
    private $profile;
    private $creator;
    private $updater;
    private $managerMember;
    private $bkashNumber;

    public function __construct(CoWorkerBkashAccountCreator $bkash_account_creator,
                                CoWorkerBkashAccountUpdater $bkash_account_updater)
    {
        $this->creator = $bkash_account_creator;
        $this->updater = $bkash_account_updater;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->member = $this->businessMember->member;
        $this->profile = $this->member->profile;
        return $this;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setBkashNumber($bkash_number)
    {
        $this->bkashNumber = $bkash_number;
        return $this;
    }

    public function getBkashNumber()
    {
        return $this->bkashNumber;
    }

    public function setManagerMember($manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function getManagerMember()
    {
        return $this->managerMember;
    }

    public function createOrUpdate()
    {
        $bkash_account = $this->businessMember->bkashInfos->last();
        if (!$bkash_account) {
            $this->creator->setBkashAccRequester($this)->create();
        } else {
            $this->updater->setSalary($bkash_account)->setBkashAccRequester($this)->update();
        }
    }
}
