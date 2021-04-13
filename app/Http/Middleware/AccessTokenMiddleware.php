<?php namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
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
    /** @var AuthUser */
    protected $authUser;

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
        $sheba_headers = getShebaRequestHeader($request);
        $is_digigo = $sheba_headers->getPortalName() == Portals::EMPLOYEE_APP;
        $now = Carbon::now()->timestamp;
        $key_name = 'digigo:debug:' . $now;

        $token = $this->getToken();
        if (!$token) {
            if ($is_digigo) Redis::set($key_name, "1: $now : null");
            return api_response($request, null, 401, ['message' => "Your session has expired. Try Login"]);
        }

        if ($this->isNotTopUpTokenRequest($request) && $error = $this->isPayloadInValid($token)) {
            if ($is_digigo) Redis::set($key_name, "4 ($error): $now : $token");
            return api_response($request, null, 401, ['message' => "Your session has expired. Try Login"]);
        }

        $access_token = $this->findAccessToken($token);
        if (!$access_token) {
            if ($is_digigo) Redis::set($key_name, "2: $now : $token");
            throw new AccessTokenDoesNotExist();
        }
        if ($this->isNotTopUpTokenRequest($request) && $access_token->isNotValid()) {
            if ($is_digigo) Redis::set($key_name, "3: $now : $token");
            throw new AccessTokenNotValidException();
        }

        $this->setAuthorizationToken($access_token);
        $this->authUser = AuthUser::create();
        $request->merge(['access_token' => $access_token, 'auth_user' => $this->authUser]);
        $this->setExtraDataToRequest($request);
        return $next($request);
    }

    protected function getToken()
    {
        return JWTAuth::getToken();
    }

    private function isNotTopUpTokenRequest($request)
    {
        return $request->url() != config('sheba.api_url') . '/v2/top-up/get-topup-token';
    }

    private function isPayloadInValid($token)
    {
        try {
            JWTAuth::getPayload($token);
            return false;
        } catch (JWTException $e) {
            return $e->getMessage();
        }
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
