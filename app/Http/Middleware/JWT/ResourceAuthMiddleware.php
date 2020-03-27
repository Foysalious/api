<?php namespace App\Http\Middleware\JWT;


use Illuminate\Http\Request;
use Sheba\Auth\Auth;
use Sheba\Auth\JWTAuth;
use Closure;
use Sheba\Authentication\AuthenticationFailedException;

class ResourceAuthMiddleware
{
    private $auth;
    private $JWTAuth;


    public function __construct(Auth $auth, JWTAuth $JWT_auth)
    {
        $this->auth = $auth;
        $this->JWTAuth = $JWT_auth;
        $this->auth->setStrategy($this->JWTAuth)->setRequest(request());
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
        try {
            $auth_user = $this->auth->authenticate();
            if ($auth_user->getResource()) {
                $request->merge(['auth_user' => $auth_user]);
                return $next($request);
            }
            return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
        } catch (AuthenticationFailedException $e) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
        }

    }
}