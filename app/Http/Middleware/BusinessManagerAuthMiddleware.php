<?php namespace App\Http\Middleware;

use App\Models\Business;
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

        $member = Member::find($payload['member_id']);
        if (!$member) $this->die(404, 'Member not found.');
        $business = Business::find((int)$request->business);

        if ($member && $business) {
            if ($member->isManager($business)) {
                $request->merge(['manager_member' => $member, 'business' => $business]);
                return $next($request);
            } else {
                return api_response($request, null, 403, ["message" => "Forbidden. You're not a manager of this business."]);
            }
        } else {
            return api_response($request, null, 404, ["message" => 'Business not found.']);
        }
    }

}