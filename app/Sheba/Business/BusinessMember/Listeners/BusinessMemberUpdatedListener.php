<?php namespace Sheba\Business\BusinessMember\Listeners;

use Sheba\Business\BusinessMember\Events\BusinessMemberUpdated;

class BusinessMemberUpdatedListener
{
    public function handle(BusinessMemberUpdated $event)
    {
        /** @var  $business_member */
        $business_member = $event->businessMember;
        return $this;
    }
}