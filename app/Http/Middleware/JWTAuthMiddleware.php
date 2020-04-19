<?php namespace App\Http\Middleware;

use Closure;
use Sheba\Auth\Auth;
use Sheba\Auth\JWTAuth;
use Sheba\Authentication\AuthenticationFailedException;
use function request;

class JWTAuthMiddleware
{
    public $auth;
    public $request;
    public $JWTAuth;

    public function __construct(Auth $auth, JWTAuth $jwt_auth)
    {
        $this->auth = $auth;
        $this->JWTAuth = $jwt_auth;
        $this->auth->setStrategy($this->JWTAuth)->setRequest(request());
    }

    public function handle($request, Closure $next)
    {
        try {
            if ($auth_user = $this->auth->authenticate()) {
                $user = $auth_user->getAvatar();
                $type = strtolower(class_basename($user));
                $request->merge([$type => $user, 'type' => $type, 'user' => $user]);
                return $next($request);
            } else {
                return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
            }
        } catch (AuthenticationFailedException $e) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
        }

    }
}
