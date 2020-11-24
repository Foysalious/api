<?php namespace App\Http\Middleware;

use App\Exceptions\NotFoundException;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use Sheba\OAuth2\AuthUser;

class BusinessManagerAuthMiddleware extends AccessTokenMiddleware
{
    protected function setExtraDataToRequest($request)
    {
        if (!$this->accessToken->accessTokenRequest->profile) return;

        $auth_user = AuthUser::create();
        $member = Member::find($auth_user->getMemberId());
        if (!$member) throw new NotFoundException('Member not found.', 404);
        $business = Business::find((int)$request->business);
        if (!$business) throw new NotFoundException('Business not found.', 404);

        $business_member = BusinessMember::where('member_id', $member->id)
            ->where('business_id', $business->id)
            ->with(['actions', 'role.businessDepartment'])
            ->first();

        $request->merge(['manager_member' => $member, 'business' => $business, 'business_member' => $business_member]);
    }

}
