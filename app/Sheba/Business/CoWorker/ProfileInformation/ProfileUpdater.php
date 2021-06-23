<?php namespace App\Sheba\Business\CoWorker\ProfileInformation;


use App\Sheba\Business\CoWorker\GetBusinessRole;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use DB;

class ProfileUpdater
{
    /** @var ProfileRequester $profile_requester*/
    private $profileRequester;
    private $profileRepository;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;

    public function __construct()
    {
        $this->profileRepository = app(ProfileRepositoryInterface::class);
        $this->businessMemberRepository = app(BusinessMemberRepositoryInterface::class);
    }

    public function setProfileRequester(ProfileRequester $profile_requester)
    {
        $this->profileRequester = $profile_requester;
        return $this;
    }

    public function update()
    {
        DB::transaction(function () {
            $this->makeData();
        });
    }

    private function makeData()
    {
        $profile_data = [
            'name' => $this->profileRequester->getName(),
            'gender' => $this->profileRequester->getGender()
        ];
        $business_member = $this->profileRequester->getBusinessMember();
        $profile = $business_member->member->profile;
        $this->profileRepository->updateRaw($profile, $profile_data);
        $designation = $this->profileRequester->getDesignation();
        $department = $this->profileRequester->getDepartment();
        $business_role = $this->getBusinessRole($department, $designation);
        $business_member_data = [
            'business_role_id' => $business_role->id,
            'join_date' => $this->profileRequester->getJoiningDate()
        ];
        $this->businessMemberRepository->update($business_member, $business_member_data);
    }

    private function getBusinessRole($department, $designation)
    {
        return (new GetBusinessRole($department, $designation))->get();
    }

}
