<?php namespace Sheba\Business\BusinessMember\Listeners;

use Sheba\Business\BusinessMember\Events\BusinessMemberCreated;
use App\Models\BusinessMember;

class BusinessMemberCreatedListener
{
    public function handle(BusinessMemberCreated $event)
    {
        /** @var BusinessMember $business_member */
        $business_member = $event->model;
        return $this;
    }
}