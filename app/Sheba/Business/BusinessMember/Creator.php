<?php namespace Sheba\Business\BusinessMember;

use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Carbon\Carbon;

class Creator
{
    /** BusinessMemberRepositoryInterface $businessMemberRepository */
    private $businessMemberRepository;
    /** @var Requester $requester */
    private $requester;

    /**
     * Creator constructor.
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


    public function create()
    {
        $data = [
            'business_id' => $this->requester->getBusinessId(),
            'member_id' => $this->requester->getMemberId(),
            'manager_id' => $this->requester->getManagerEmployee(),
            'business_role_id' => $this->requester->getRole()
        ];
        return $this->businessMemberRepository->create($data);
    }
}