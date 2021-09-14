<?php namespace Sheba\Business\BusinessMember;

use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Creator
{
    /** @var BusinessMemberRepositoryInterface $businessMemberRepository */
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
            'business_role_id' => $this->requester->getRole(),
            'status' => $this->requester->getStatus(),
            'join_date' => $this->requester->getJoinDate()
        ];
        if ($this->requester->getIsSuper()) $data['is_super'] = $this->requester->getIsSuper();
        return $this->businessMemberRepository->create($data);
    }
}