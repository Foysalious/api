<?php namespace Sheba\Business\Support;


use App\Models\Member;
use Sheba\Dal\Support\SupportRepositoryInterface;

class Creator
{
    private $supportRepository;
    private $member;
    private $description;

    public function __construct(SupportRepositoryInterface $support_repository)
    {
        $this->supportRepository = $support_repository;
    }

    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function create()
    {
        return $this->supportRepository->create([
            'member_id' => $this->member->id,
            'long_description' => $this->description
        ]);
    }
}