<?php namespace Sheba\Repositories\Business;

use App\Models\Member;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class MemberRepository extends BaseRepository implements MemberRepositoryInterface
{
    public function __construct(Member $member)
    {
        parent::__construct();
        $this->setModel($member);
    }
}