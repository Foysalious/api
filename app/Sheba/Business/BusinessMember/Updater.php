<?php namespace Sheba\Business\BusinessMember;

use App\Models\BusinessMember;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use App\Models\BusinessRole;

class Updater
{

    /** BusinessMemberRepositoryInterface $businessMemberRepository */
    private $businessMemberRepository;
    /** @var Requester $requester */
    private $requester;
    /** BusinessMember $businessMember */
    private $businessMember;


    /**
     * Updater constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->businessMemberRepository = $business_member_repository;
    }

    /**
     * @param Requester $requester
     * @return $this
     */
    public function setRequester(Requester $requester)
    {
        $this->requester = $requester;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update()
    {
        $data = [
            'business_role_id' => $this->requester->getRole(),
            'manager_id' => $this->requester->getManagerEmployee()
        ];
        return $this->businessMemberRepository->update($this->businessMember, $data);
    }
}