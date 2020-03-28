<?php namespace App\Http\Middleware\Resource;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResourceJWTAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();
        } catch (JWTException $e) {
            return api_response($request, null, 401);
        }
        if ($payload) {

            return $next($request);
        } else return api_response($request, null, 403);
    }
}