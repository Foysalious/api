<?php namespace Sheba\Business\BusinessMember\Listeners;

use Sheba\Business\BusinessMember\Events\BusinessMemberDeleted;
use App\Models\BusinessMember;

class BusinessMemberDeletedListener
{
    public function handle(BusinessMemberDeleted $event)
    {
        /** @var BusinessMember $business_member */
        $business_member = $event->model;
        return $this;
    }
}