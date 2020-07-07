<?php namespace Sheba\Business\CoWorker;

use App\Models\BusinessMember;
use App\Models\Profile;

class ProfileCompletionCalculator
{
    /** @var BusinessMember $businessMember */
    private $businessMember;
    /** @var Profile $profile */
    private $profile;

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->profile = $this->businessMember->member->profile;

        return $this;
    }

    public function getDigiGoScore()
    {
        return $this->calculateBasic();
    }

    public function getBasicScore()
    {
        return $this->calculateBasic();
    }

    public function getOfficialScore()
    {
    }

    public function getPersonalScore()
    {
    }

    public function getFinancialScore()
    {
    }

    public function getEmergencyScore()
    {
    }

    private function calculateBasic()
    {
        if (!$this->profile->name) return 0;
        if (!$this->profile->email) return 0;
        if (!$this->businessMember->role) return 0;
        if (!$this->businessMember->department()) return 0;

        return 100;
    }

    private function calculateOfficial()
    {
    }

    private function calculatePersonal()
    {
    }

    private function calculateFinancial()
    {
    }

    private function calculateEmergency()
    {
    }
}
