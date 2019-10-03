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
}