<?php namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthentication
{
    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();
        } catch (JWTException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => request()->all(), 'token' => request()->headers, 'ip' => request()->ip()]);
            $sentry->captureException($e);
            return api_response($request, null, 401);
        }
        if ($payload) {
            if (isset($payload['profile'])) {
                $profile = Profile::find($payload['profile']['id']);
                if ($profile) $request->merge(['profile' => $profile, 'auth_info' => $payload]);
            }
            return $next($request);
        } else {
            $sentry = app('sentry');
            $sentry->user_context(['request' => request()->all(), 'token' => request()->headers, 'ip' => request()->ip()]);
            $sentry->captureException(new \Exception("Forbidden"));
            return api_response($request, null, 403);
        }
    }
}
