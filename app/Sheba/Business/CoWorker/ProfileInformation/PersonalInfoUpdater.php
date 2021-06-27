<?php namespace App\Sheba\Business\CoWorker\ProfileInformation;


use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use DB;

class PersonalInfoUpdater
{
    /*** @var MemberRepositoryInterface $memberRepository*/
    private $memberRepository;
    /*** @var ProfileRequester $profileRequester*/
    private $profileRequester;
    /** @var ProfileRepositoryInterface $profileRepository*/
    private $profileRepository;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;

    public function __construct()
    {
        $this->memberRepository = app(MemberRepositoryInterface::class);
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
        $this->makeData();
    }

    private function makeData()
    {
        $business_member = $this->profileRequester->getBusinessMember();
        $member = $business_member->member;
        $profile = $member->profile;
        $profile_data = $this->makeProfileData();
        $member_data = $this->makeMemberData();
        $business_member_data = $this->makeBusinessMemberData();
        DB::transaction(function () use ($member, $business_member, $profile, $profile_data, $member_data, $business_member_data) {
            $this->memberRepository->update($member, $member_data);
            $this->profileRepository->update($profile, $profile_data);
            if ($business_member_data) $this->businessMemberRepository->update($business_member, $business_member_data);
        });
    }

    private function makeProfileData()
    {
        return [
          'dob' => $this->profileRequester->getDateOfBirth(),
          'address' => $this->profileRequester->getAddress(),
          'nationality' => $this->profileRequester->getNationality(),
          'nid_no' => $this->profileRequester->getNidNo(),
          'passport_no' => $this->profileRequester->getPassportNo(),
          'blood_group' => $this->profileRequester->getBloodGroup()
        ];
    }

    private function makeMemberData()
    {
        return [
          'social_links' =>   $this->profileRequester->getSocialLinks()
        ];
    }

    private function makeBusinessMemberData()
    {
        return $this->profileRequester->getMobile() ? [
          'mobile' => $this->profileRequester->getMobile()
        ] : null;
    }

}
