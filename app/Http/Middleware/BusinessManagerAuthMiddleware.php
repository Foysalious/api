<?php namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Closure;

class BusinessManagerAuthMiddleware
{
    private $member;

    public function handle($request, Closure $next)
    {
        try {
            $payload = [];
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();
        } catch (JWTException $e) {
            return api_response($request, null, 401);
        }

        $member_id = array_key_exists('member_id', $payload) ? $payload['member_id'] : $payload['member']['id'];
        $member = Member::find($member_id);
        if (!$member) return response()->json(['message' => 'Member not found.', 'code' => 404]);
        $business = Business::find((int)$request->business);

        if ($member && $business) {
            $business_member = BusinessMember::where([['member_id', $member->id], ['business_id', $business->id]])
                ->with(['actions', 'role.businessDepartment'])->first();
            $request->merge(['manager_member' => $member, 'business' => $business, 'business_member' => $business_member]);
            return $next($request);
        } else {
            return api_response($request, null, 404, ["message" => 'Business not found.']);
        }
    }

}
