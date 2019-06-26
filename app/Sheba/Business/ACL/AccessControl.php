<?php namespace App\Sheba\Business\ACL;

use App\Models\BusinessMember;

class AccessControl
{
    /** @var BusinessMember */
    private $businessMember;

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function hasAccess($action_name)
    {
        return $this->businessMember->isSuperAdmin() || $this->businessMember->actions()->where('tag', config('business.actions.' . $action_name))->first();
    }
}