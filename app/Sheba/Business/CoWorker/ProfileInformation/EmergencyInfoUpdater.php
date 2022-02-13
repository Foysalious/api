<?php namespace App\Sheba\Business\CoWorker\ProfileInformation;


use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use DB;

class EmergencyInfoUpdater
{
    /*** @var MemberRepositoryInterface $memberRepository*/
    private $memberRepository;
    /*** @var ProfileRequester $profileRequester*/
    private $profileRequester;

    public function __construct()
    {
        $this->memberRepository = app(MemberRepositoryInterface::class);
    }

    public function setProfileRequester(ProfileRequester $profile_requester)
    {
        $this->profileRequester = $profile_requester;
        return $this;
    }

    public function update()
    {
        $emergency_contact_data = $this->makeData();
        $member = $this->profileRequester->getBusinessMember()->member;
        DB::transaction(function () use ($member, $emergency_contact_data){
            $this->memberRepository->update($member, $emergency_contact_data);
        });
    }

    private function makeData()
    {
        return [
            'emergency_contract_person_name' => $this->profileRequester->getEmergencyContactName(),
            'emergency_contract_person_number' => $this->profileRequester->getEmergencyContactMobile(),
            'emergency_contract_person_relationship' => $this->profileRequester->getEmergencyContactRelation()
        ];
    }

}
