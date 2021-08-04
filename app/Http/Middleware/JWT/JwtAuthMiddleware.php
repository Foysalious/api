<?php namespace App\Http\Middleware\JWT;

use Closure;
use Sheba\OAuth2\AuthUser;

abstract class JwtAuthMiddleware
{
    protected $authUser;

    public function __construct()
    {
        $this->authUser = AuthUser::create();
    }

    public function handle($request, Closure $next)
    {
        if (!$this->hasPassedAuthCheck()) return api_response($request, null, 409, ['message' => 'Unauthorized request']);
        $request->merge(['auth_user' => $this->authUser]);
        return $next($request);
    }

    abstract protected function hasPassedAuthCheck();
}