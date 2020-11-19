<?php namespace App\Http\Middleware;


use App\Exceptions\NotFoundException;
use App\Models\BusinessMember;
use App\Models\Member;
use Sheba\OAuth2\AuthUser;

class MemberAuthMiddleware extends AccessTokenMiddleware
{
    protected function setExtraDataToRequest($request)
    {
        if (!$this->accessToken->accessTokenRequest->profile) return;

        $auth_user = AuthUser::create();
        $member = Member::find($auth_user->getMemberId());
        if (!$member) throw new NotFoundException('Member not found.', 404);
        if ($member->id != (int)$request->member) throw new NotFoundException("Member doesn't match .", 409);
        $request->merge(['member' => $member]);
        $business = $member->businesses->first();
        if ($business) {
            $request->merge(['business' => $business]);
            $business_member = BusinessMember::where([['member_id', $member->id], ['business_id', $business->id]])
                ->with(['actions', 'role.businessDepartment'])->first();
            $request->merge(['business_member' => $business_member]);
        }
    }
}
