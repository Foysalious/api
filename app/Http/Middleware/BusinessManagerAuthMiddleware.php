<?php namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Sheba\OAuth2\AuthUser;
use Closure;
use Sheba\OAuth2\SomethingWrongWithToken;

class BusinessManagerAuthMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @return JsonResponse|mixed
     * @throws SomethingWrongWithToken
     */
    public function handle($request, Closure $next)
    {
        $auth_user = AuthUser::create();
        $member_id = $auth_user->getMemberId();
        $member = Member::find($member_id);
        if (!$member) return response()->json(['message' => 'Member not found.', 'code' => 404]);
        $business = Business::find((int)$request->business);

        if (!$business) return api_response($request, null, 404, ["message" => 'Business not found.']);

        $business_member = BusinessMember::where('member_id', $member->id)
            ->where('business_id', $business->id)
            ->with(['actions', 'role.businessDepartment'])
            ->first();

        $request->merge(['manager_member' => $member, 'business' => $business, 'business_member' => $business_member]);

        return $next($request);
    }
}
