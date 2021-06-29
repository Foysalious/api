<?php namespace Sheba\Business\CoWorker\Validation;

use Sheba\Business\CoWorker\Creator;
use Sheba\Helpers\HasErrorCodeAndMessage;
use App\Models\BusinessMember;
use App\Models\Business;
use App\Models\Member;
use Sheba\Repositories\ProfileRepository;

class CoWorkerExistenceCheck
{
    use HasErrorCodeAndMessage;

    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /**  @var BusinessMember $businessMember */
    private $businessMember;
    /**  @var Business $business */
    private $business;
    /** @var Member $member */
    private $member;
    private $email;

    public function __construct(ProfileRepository $profile_repository)
    {
        $this->profileRepository = $profile_repository;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->member = $this->businessMember->member;
        $this->isActiveOrInvitedInAnotherBusiness();
        return $this;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return $this
     */
    private function isActiveOrInvitedInAnotherBusiness()
    {
        if ($this->member->businesses()->where('businesses.id', '<>', $this->business->id)->count() > 0) {
            $this->setError(422, "This person is already active or invited in another business");
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function checkEmailUsability()
    {
        if (!$this->email) return $this;

        $profile = $this->profileRepository->checkExistingEmail($this->email);
        if (!$profile) return $this;
        if (!$profile->member) return $this;

        $this->isTheEmployeeAlreadyInMyActiveOrInvitedList($profile);
        $this->isTheEmployeeAlreadyInMyInactiveList($profile);
        $this->isTheEmployeeAlreadyOthersActiveOrInvitedList($profile);

        return $this;
    }

    private function isTheEmployeeAlreadyInMyActiveOrInvitedList($profile)
    {
        if ($profile->member->businesses()->where('businesses.id', $this->business->id)->count() > 0) {
            $this->setError(421, "This employee is already added to your business");
        }
    }

    private function isTheEmployeeAlreadyInMyInactiveList($profile)
    {
        if ($profile->member->inactiveBusinesses()->where('businesses.id', $this->business->id)->count() > 0) {
            $this->setError(409, "This employee exists in your inactive list. Do you want to activate again?");
        }
    }

    private function isTheEmployeeAlreadyOthersActiveOrInvitedList($profile)
    {
        if ($profile->member->businesses()->where('businesses.id', '<>', $this->business->id)->count() > 0) {
            $this->setError(422, "This employee is already added in another business");
        }
    }

}