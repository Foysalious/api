<?php namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\Member;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Closure;

class BusinessAuthMiddleware
{
    private $member;

    public function handle($request, Closure $next)
    {
        $payload = [];
        try {

            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();
        } catch (JWTException $e) {
            $this->die(401, $e->getMessage());
        }
        $this->member = Member::find($payload['member_id']);
        if (!$this->member) $this->die(404, 'Member not found.');

        $business = Business::find((int)$request->business);
        if ($this->member && $business) {
            if ($this->member->isManager($business)) {
                $request->merge(['manager_member' => $this->member, 'business' => $business]);
                return $next($request);
            } else {
                return api_response($request, null, 403, ["message" => "Forbidden. You're not a manager of this business."]);
            }
        } else {
            return api_response($request, null, 404, ["message" => 'Business not found.']);
        }
    }

}