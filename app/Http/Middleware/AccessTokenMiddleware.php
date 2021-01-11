<?php namespace App\Http\Middleware;

use Sheba\AccessToken\Exception\AccessTokenNotValidException;
use Sheba\AccessToken\Exception\AccessTokenDoesNotExist;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
use Sheba\Dal\AuthorizationToken\AuthorizationTokenRepositoryInterface;
use Sheba\OAuth2\AuthUser;
use Sheba\Portals\Portals;
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
        $sheba_headers = getShebaRequestHeader($request);
        $is_digigo = $sheba_headers->getPortalName() == Portals::EMPLOYEE_APP;
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                if ($is_digigo) logError(new \Exception("Digigo debug 1."), $request);
                return api_response($request, null, 401, ['message' => "Your session has expired. Try Login"]);
            }
            if ($request->url() != config('sheba.api_url') . '/v2/top-up/get-topup-token') JWTAuth::getPayload($token);
            $access_token = $this->findAccessToken($token);
            if (!$access_token) {
                if ($is_digigo) logError(new \Exception("Digigo debug 2."), $request);
                throw new AccessTokenDoesNotExist();
            }
            if ($request->url() != config('sheba.api_url') . '/v2/top-up/get-topup-token' && !$access_token->isValid()) {
                if ($is_digigo) logError(new \Exception("Digigo debug 3."), $request);
                throw new AccessTokenNotValidException();
            }
            $this->setAuthorizationToken($access_token);
            $request->merge(['access_token' => $access_token, 'auth_user' => AuthUser::create()]);
        } catch (JWTException $e) {
            if ($is_digigo) logError(new \Exception("Digigo debug 4."), $request);
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
}
