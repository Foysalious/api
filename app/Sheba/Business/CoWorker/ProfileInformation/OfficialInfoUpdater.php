<?php namespace App\Sheba\Business\CoWorker\ProfileInformation;


use DB;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class OfficialInfoUpdater
{
    /** @var ProfileRequester $profile_requester*/
    private $profileRequester;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;

    public function __construct()
    {
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
        $business_member = $this->profileRequester->getBusinessMember();
        $business_member_data = [
          'manager_id' =>  $this->profileRequester->getManager(),
          'employee_id' => $this->profileRequester->getEmployeeId(),
          'employee_type' => $this->profileRequester->getEmployeeType(),
          'grade' => $this->profileRequester->getGrade()
        ];
        $this->businessMemberRepository->update($business_member, $business_member_data);
    }

}
