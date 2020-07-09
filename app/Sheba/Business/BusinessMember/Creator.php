<?php namespace Sheba\Business\BusinessMember;

use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

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

    /**
     * @return $this
     */
    public function create()
    {
        return $this;
    }
}