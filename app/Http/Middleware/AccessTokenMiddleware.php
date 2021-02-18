<?php namespace App\Http\Middleware;

use Illuminate\Http\Request;
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

    public function handle(Request $request, Closure $next)
    {
        try {
            if ($this->runningUnitTests()) {
                JWTAuth::setRequest($request);
            }

            $token = JWTAuth::getToken();
            if (!$token) return api_response($request, null, 401, ['message' => "Your session has expired. Try Login"]);
            if ($request->url() != config('sheba.api_url') . '/v2/top-up/get-topup-token') JWTAuth::getPayload($token);
            $access_token = $this->findAccessToken($token);
            if (!$access_token) throw new AccessTokenDoesNotExist();
            if ($request->url() != config('sheba.api_url') . '/v2/top-up/get-topup-token' && !$access_token->isValid()) throw new AccessTokenNotValidException();
            $this->setAuthorizationToken($access_token);
            $request->merge(['access_token' => $access_token, 'auth_user' => AuthUser::create()]);
        } catch (JWTException $e) {
            return api_response($request, null, 401, ['message' => "Your session has expired. Try Login"]);
        }
        $this->setExtraDataToRequest($request);
        return $next($request);
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

    private function runningUnitTests()
    {
        $app = app();
        return $app->runningInConsole() && $app->runningUnitTests();
    }
}
