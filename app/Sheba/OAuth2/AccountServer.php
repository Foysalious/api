<?php namespace Sheba\OAuth2;

use GuzzleHttp\Client;

class AccountServer
{
    /** @var AccountServerClient */
    private $client;

    public function __construct(AccountServerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param $avatar
     * @param $type
     * @return string
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function getTokenByAvatar($avatar, $type)
    {
        return $this->getTokenByIdAndRememberToken($avatar->id, $avatar->remember_token, $type);
    }

    /**
     * @param $id
     * @param $remember_token
     * @param $type
     * @return string
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function getTokenByIdAndRememberToken($id, $remember_token, $type)
    {
        $data = $this->client->get("api/v3/token/generate?type=$type&token=$remember_token&type_id=$id");
        return $data['token'];
    }

    /**
     * @param $old_token
     * @return mixed
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function getRefreshToken($old_token)
    {
        $data = $this->client->get("api/v3/token/refresh?token=$old_token");
        return $data['token'];
    }

    /**
     * @param $mobile
     * @param $password
     * @return string
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function getTokenByMobileAndPassword($mobile, $password)
    {
        return $this->getTokenByIdentityAndPassword($mobile, $password);
    }

    /**
     * @param $email
     * @param $password
     * @return string
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function getTokenByEmailAndPassword($email, $password)
    {
        return $this->getTokenByIdentityAndPassword($email, $password);
    }

    /**
     * @param $email
     * @param $password
     * @return string
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function getTokenByEmailAndPasswordV2($email, $password)
    {
        $data = $this->client->post("api/v3/profile/login", ['email' => $email, 'password' => $password]);
        return $data['token'];
    }

    /**
     * @param $identity
     * @param $password
     * @return mixed
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function getTokenByIdentityAndPassword($identity, $password)
    {
        $data = $this->client->post("api/v3/login", [
            'identity' => $identity,
            'password' => $password
        ]);
        return $data['token'];
    }

    /**
     * @param $avatar_type
     * @param $mobile
     * @param $password
     * @return string
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function createAvatarAndGetTokenByMobileAndPassword($avatar_type, $mobile, $password)
    {
        return $this->createAvatarAndGetTokenByIdentityAndPassword($avatar_type, $mobile, $password);
    }

    /**
     * @param $avatar_type
     * @param $email
     * @param $password
     * @return string
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function createAvatarAndGetTokenByEmailAndPassword($avatar_type, $email, $password)
    {
        return $this->createAvatarAndGetTokenByIdentityAndPassword($avatar_type, $email, $password);
    }

    /**
     * @param $avatar_type
     * @param $identity
     * @param $password
     * @return mixed
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function createAvatarAndGetTokenByIdentityAndPassword($avatar_type, $identity, $password)
    {
        $data = $this->client->post("api/v3/login", [
            'identity' => $identity,
            'password' => $password,
            'create_avatar' => true,
            'avatar_type' => $avatar_type
        ]);
        return $data['token'];
    }

    /**
     * @param $avatar_type
     * @param $name
     * @param $email
     * @param $password
     * @return string
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function createProfileAndAvatarAndGetTokenByEmailAndPassword($avatar_type, $name, $email, $password)
    {
        return $this->createProfileAndAvatarAndGetTokenByIdentityAndPassword($avatar_type, $name, $email, $password);
    }

    /**
     * @param $avatar_type
     * @param $name
     * @param $identity
     * @param $password
     * @return mixed
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function createProfileAndAvatarAndGetTokenByIdentityAndPassword($avatar_type, $name, $identity, $password)
    {
        $data = $this->client->post("api/v3/register", [
            'name' => $name,
            'email' => $identity,
            'password' => $password,
            'create_avatar' => true,
            'avatar_type' => $avatar_type
        ]);
        return $data['token'];
    }

    /**
     * @param $token
     * @return array
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function sendEmailVerificationLink($token)
    {
        return $this->client->get("api/v3/send-verification-link?token=$token");
    }

    public function logout($token, $reason)
    {
        return (new Client())->post(rtrim(config('account.account_url'), '/') . "/api/v1/logout", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'form_params' => [
                'reason' => $reason
            ]
        ]);
    }

    public function passwordAuthenticate($mobile, $email, $password, $purpose)
    {
        $data = [
            'password' => $password,
            'purpose' => $purpose
        ];
        if (!empty($email)) $data['email'] = $email;
        if (!empty($mobile)) $data['mobile'] = $mobile;
        return (new Client())->post(rtrim(config('account.account_url'), '/') . "/api/v1/authenticate/password", [
            'form_params' => $data
        ]);
    }

    public function getAuthenticateRequests($token, $purpose)
    {
        return (new Client())->get(rtrim(config('account.account_url'), '/') . "/api/v1/authenticate/password/requests", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'query' => [
                'purpose' => $purpose
            ]
        ]);
    }

    /**
     * @param $code
     * @return string
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function getTokenByShebaAccountKit($code)
    {
        $data = $this->client->post("api/v3/profile/authenticate/sheba-accountkit", ['code' => $code]);
        return $data['token'];
    }
}
