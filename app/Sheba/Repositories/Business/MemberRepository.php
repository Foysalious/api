<?php namespace Sheba\Repositories\Business;

use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Repositories\BaseRepository;
use App\Models\Member;

class MemberRepository extends BaseRepository implements MemberRepositoryInterface
{
    public function __construct(Member $member)
    {
        parent::__construct();
        $this->setModel($member);
    }

    public function makeMember($profile)
    {
        $this->setModifier($profile);
        $member = new Member();
        $member->profile_id = $profile->id;
        $member->remember_token = str_random(255);
        $member->save();
        return $member;
    }
}