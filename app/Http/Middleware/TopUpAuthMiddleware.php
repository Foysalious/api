<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sheba\Auth\Auth;
use Sheba\Auth\JWTAuth;
use Sheba\Auth\RememberTokenAuth;

class TopUpAuthMiddleware
{
    public $auth;
    public $request;
    public $rememberTokenAuth;
    public $JWTAuth;

    public function __construct(Auth $auth, RememberTokenAuth $remember_token_auth, JWTAuth $jwt_auth)
    {
        $this->auth = $auth;
        $this->rememberTokenAuth = $remember_token_auth;
        $this->JWTAuth = $jwt_auth;
        $this->auth->setStrategy($this->JWTAuth)->setRequest(\request());
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $this->auth->authenticate();
        if ($user) {
            $type = strtolower(class_basename($user));
            $request->merge([$type => $user, 'type' => $type, 'user' => $user]);
            return $next($request);
        } else {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
        }
    }
}
