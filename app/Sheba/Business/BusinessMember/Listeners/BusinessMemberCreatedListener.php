<?php namespace Sheba\Business\BusinessMember\Listeners;

use Sheba\Business\BusinessMember\Events\BusinessMemberCreated;

class BusinessMemberCreatedListener
{
    public function handle(BusinessMemberCreated $event)
    {
        /** @var  $business_member */
        $business_member = $event->businessMember;
        return $this;
    }
}