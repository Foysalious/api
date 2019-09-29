<?php namespace Sheba\Repositories\Business;

use App\Models\BusinessMember;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class BusinessMemberRepository extends BaseRepository implements BusinessMemberRepositoryInterface
{
    public function __construct(BusinessMember $business_member)
    {
        parent::__construct();
        $this->setModel($business_member);
    }
}