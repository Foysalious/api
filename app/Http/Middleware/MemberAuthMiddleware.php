<?php namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Closure;

class MemberAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $payload = [];
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();
        } catch (JWTException $e) {
            return api_response($request, null, 401);
        }

        $member = Member::find($payload['member_id']);
        if (!$member) return response()->json(['message' => 'Member not found.', 'code' => 404]);

        if ($member->id == (int)$request->member) {
            $request->merge(['member' => $member]);
            $business = $member->businesses->first();
            $request->merge(['business' => $business]);
            $business_member = BusinessMember::where([['member_id', $member->id], ['business_id', $business->id]])
                ->with(['actions', 'role.businessDepartment'])->first();
            $request->merge(['business_member' => $business_member]);
            return $next($request);
        }
        return response()->json(['message' => 'unauthorized', 'code' => 409]);
    }
}
