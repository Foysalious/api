<?php namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use Illuminate\Http\Request;
use Sheba\OAuth2\AuthUser;
use Sheba\OAuth2\SomethingWrongWithToken;
use Closure;

class MemberAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws SomethingWrongWithToken
     */
    public function handle($request, Closure $next)
    {
        $auth_user = AuthUser::create();
        $member = Member::find($auth_user->getMemberId());
        if (!$member) return response()->json(['message' => 'Member not found.', 'code' => 404]);

        if ($member->id == (int)$request->member) {
            $request->merge(['member' => $member]);
            /** @var Business $business */
            $business = $member->businesses->first();
            if ($business) {
                $request->merge(['business' => $business]);
                $business_member = BusinessMember::where([['member_id', $member->id], ['business_id', $business->id]])
                    ->with(['actions', 'role.businessDepartment'])->first();
                $request->merge(['business_member' => $business_member]);
            }
            return $next($request);
        }

        return response()->json(['message' => 'unauthorized', 'code' => 409]);
    }
}
