<?php namespace Sheba\AccessToken;


use Carbon\Carbon;
use Sheba\Dal\AccessToken\AccessToken;
use Sheba\Dal\AccessToken\AccessTokenRepositoryInterface;
use Sheba\Dal\AccessTokenRequest\AccessTokenRequest;

class Creator
{
    /** @var AccessTokenRequest */
    private $accessTokenRequest;
    /** @var AccessToken */
    private $accessToken;
    private $token;
    /** @var Carbon */
    private $validTill;
    /** @var AccessTokenRepositoryInterface */
    private $accessTokenRepository;

    public function __construct(AccessTokenRepositoryInterface $access_token_repository)
    {
        $this->accessTokenRepository = $access_token_repository;
    }

    /**
     * @param AccessTokenRequest $accessTokenRequest
     * @return Creator
     */
    public function setAccessTokenRequest($accessTokenRequest)
    {
        $this->accessTokenRequest = $accessTokenRequest;
        return $this;
    }

    /**
     * @param AccessToken $accessToken
     * @return Creator
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @param mixed $token
     * @return Creator
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @param Carbon $validTill
     * @return Creator
     */
    public function setValidTill($validTill)
    {
        $this->validTill = $validTill;
        return $this;
    }

    public function create()
    {
        $access_token = $this->accessTokenRepository->builder()->create([
            'access_token_request_id' => $this->accessTokenRequest->id,
            'parent_id' => $this->accessToken ? $this->accessToken->id : null,
            'valid_till' => $this->validTill->toDateTimeString(),
            'token' => $this->token
        ]);
        if ($this->accessToken) $this->accessToken->update(['is_active' => 0]);
        return $access_token;
    }
}