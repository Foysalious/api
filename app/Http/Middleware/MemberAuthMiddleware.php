<?php namespace App\Http\Middleware;

use App\Models\Member;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Closure;

class MemberAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
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
        if (!$member) $this->die(404, 'Member not found.');

        if ($member->id == (int)$request->member) {
            return $next($request);
        }
        return response()->json(['message' => 'unauthorized', 'code' => 409]);
    }
}
