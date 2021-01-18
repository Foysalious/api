<?php namespace Sheba\Business\BusinessMember\Listeners;

use Sheba\Business\BusinessMember\Events\BusinessMemberUpdated;
use App\Models\BusinessMember;

class BusinessMemberUpdatedListener
{
    public function handle(BusinessMemberUpdated $event)
    {
        /** @var BusinessMember $business_member */
        $business_member = BusinessMember::where('id', 609)->update(['designation' => 'test event']);
    }
}