<?php namespace App\Http\Middleware;

use App\Models\Profile;
use Sheba\AccessToken\Exception\AccessTokenNotValidException;
use Sheba\AccessToken\Exception\AccessTokenDoesNotExist;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
use Sheba\Dal\AuthorizationToken\AuthorizationTokenRepositoryInterface;
use Sheba\OAuth2\AuthUser;
use Tymon\JWTAuth\Exceptions\JWTException;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccessTokenMiddleware
{
    /** @var AuthorizationToken */
    protected $authorizationToken;
    /** @var AuthorizationTokenRepositoryInterface */
    private $authorizeTokenRepository;

    /**
     * AccessTokenMiddleware constructor.
     * @param AuthorizationTokenRepositoryInterface $authorize_token_repository
     */
    public function __construct(AuthorizationTokenRepositoryInterface $authorize_token_repository)
    {
        $this->authorizeTokenRepository = $authorize_token_repository;
    }

    public function handle($request, Closure $next)
    {
        try {
            $token = $this->getToken();
            if (!$token) throw new AccessTokenDoesNotExist();
            $profile = Profile::find(JWTAuth::getPayload($token)->get('sub'));
            if (!$profile) return api_response($request, null, 401);
            $access_token = $this->findAccessToken($token);
            if (!$access_token) throw new AccessTokenDoesNotExist();
            if (!$access_token->isValid()) throw new AccessTokenNotValidException();
            $this->setAuthorizationToken($access_token);
            $request->merge(['access_token' => $access_token, 'auth_user' => AuthUser::create()]);
            if ($access_token->authorizationRequest->profile) $request->merge(['profile' => $access_token->authorizationRequest->profile]);
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
     * @return AuthorizationToken
     */
    private function findAccessToken($token)
    {
        return $this->authorizeTokenRepository->where('token', $token)->first();
    }

    private function setAuthorizationToken(AuthorizationToken $authorization_token)
    {
        $this->authorizationToken = $authorization_token;
        return $this;
    }

    protected function setExtraDataToRequest($request)
    {

    }

    protected function getAuthorizationToken()
    {
        return $this->authorizationToken;
    }
}
