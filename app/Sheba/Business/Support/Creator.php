<?php namespace Sheba\Business\Support;


use App\Models\Member;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use DB;

class Creator
{
    private $supportRepository;
    private $businessMemberRepository;
    private $member;
    private $description;

    public function __construct(SupportRepositoryInterface $support_repository, BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->supportRepository = $support_repository;
        $this->businessMemberRepository = $business_member_repository;
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
        $support = null;
        DB::transaction(function () use (&$support) {
            $support = $this->supportRepository->create([
                'member_id' => $this->member->id,
                'long_description' => $this->description
            ]);
            $this->notifySuperAdmins($support);
        });
        return $support;
    }

    private function notifySuperAdmins($support)
    {
        $super_admins = $this->businessMemberRepository->where('is_super', 1)
            ->where('business_id', $this->member->businesses()->first()->id)->get();
        foreach ($super_admins as $super_admin) {
            notify()->member($super_admin->member)->send([
                'title' => $this->member->profile->name . ' #' . $this->member->id . ' has created a Support Ticket',
                'type' => 'warning',
                'event_type' => 'Sheba\Dal\Support\Model',
                'event_id' => $support->id
            ]);
        }

    }
}