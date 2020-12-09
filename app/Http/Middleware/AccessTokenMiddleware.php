<?php namespace App\Http\Middleware;

use Sheba\AccessToken\Exception\AccessTokenNotValidException;
use Sheba\AccessToken\Exception\AccessTokenDoesNotExist;
use Sheba\Dal\AccessToken\AccessToken;
use Sheba\Dal\AccessToken\AccessTokenRepository;
use Sheba\OAuth2\AuthUser;
use Sheba\Portals\Portals;
use Tymon\JWTAuth\Exceptions\JWTException;
use Closure;

class AccessTokenMiddleware
{
    /** @var AccessToken */
    protected $accessToken;
    /** @var AccessTokenRepository */
    private $accessTokenRepository;

    /**
     * AccessTokenMiddleware constructor.
     * @param AccessTokenRepository $access_token_repository
     */
    public function __construct(AccessTokenRepository $access_token_repository)
    {
        $this->accessTokenRepository = $access_token_repository;
    }

    public function handle($request, Closure $next)
    {
        try {
            $token = $this->getToken();
            if (!$token) throw new AccessTokenDoesNotExist();
            $access_token = $this->findAccessToken($token);
            if (!$access_token) throw new AccessTokenDoesNotExist();
            if ($request->hasHeader('portal-name') && $request->header('portal-name') == Portals::EMPLOYEE_APP) {
                if ($access_token->isBlacklisted()) throw new AccessTokenNotValidException();
            } else {
                if (!$access_token->isValid()) throw new AccessTokenNotValidException();
            }
            $this->setAccessToken($access_token);
            $request->merge(['access_token' => $access_token, 'auth_user' => AuthUser::create()]);
            if ($access_token->accessTokenRequest->profile) $request->merge(['profile' => $access_token->accessTokenRequest->profile]);
        } catch (JWTException $e) {
            return api_response($request, null, 401);
        }
        $this->setExtraDataToRequest($request);
        return $next($request);
    }

    protected function getToken()
    {
        $token = AuthUser::getToken();
        return $token ? AuthUser::getToken()->get() : null;
    }

    /**
     * @param $token
     * @return AccessToken
     */
    private function findAccessToken($token)
    {
        return $this->accessTokenRepository->where('token', $token)->first();
    }

    private function setAccessToken(AccessToken $access_token)
    {
        $this->accessToken = $access_token;
        return $this;
    }

    protected function setExtraDataToRequest($request)
    {

    }

    protected function getAccessToken()
    {
        return $this->accessToken;
    }
}
