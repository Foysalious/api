<?php namespace Sheba\Business\BusinessMember\Listeners;

use Sheba\Business\BusinessMember\Events\BusinessMemberDeleted;

class BusinessMemberDeletedListener
{
    public function handle(BusinessMemberDeleted $event)
    {
        /** @var  $business_member */
        $business_member = $event->businessMember;
        return $this;
    }
}