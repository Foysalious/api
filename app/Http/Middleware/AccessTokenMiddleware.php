<?php namespace App\Http\Middleware;

use Sheba\AccessToken\Exception\AccessTokenNotValidException;
use Sheba\AccessToken\Exception\AccessTokenDoesNotExist;
use Sheba\Dal\AccessToken\AccessToken;
use Sheba\Dal\AccessToken\AccessTokenRepository;
use Sheba\OAuth2\AuthUser;
use Tymon\JWTAuth\Exceptions\JWTException;
use Closure;

abstract class AccessTokenMiddleware
{
    private $accessTokenRepository;

    public function __construct(AccessTokenRepository $access_token_repository)
    {
        $this->accessTokenRepository = $access_token_repository;
    }

    public function handle($request, Closure $next)
    {
        try {
            $access_token = $this->findAccessToken($this->getToken());
            if (!$access_token) throw new AccessTokenDoesNotExist();
            if (!$access_token->isValid()) throw new AccessTokenNotValidException();
            $request->merge(['access_token' => $access_token]);
            if ($access_token->accessTokenRequest->profile) $request->merge(['profile' => $access_token->accessTokenRequest->profile]);
        } catch (JWTException $e) {
            return api_response($request, null, 401);
        }
        $this->setExtraDataToRequest($request);
        return $next($request);
    }

    protected function getToken()
    {
        return AuthUser::getToken()->get();
    }

    /**
     * @param $token
     * @return AccessToken
     */
    private function findAccessToken($token)
    {
        return $this->accessTokenRepository->where('token', $token)->first();
    }

    abstract protected function setExtraDataToRequest($request);
}