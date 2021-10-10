<?php namespace Sheba\OAuth2;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

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
     * @throws WrongPinError
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
     * @throws WrongPinError
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
     * @throws WrongPinError
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
     * @throws WrongPinError
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
     * @throws WrongPinError
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
     * @throws WrongPinError
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
     * @throws WrongPinError
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
     * @throws WrongPinError
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
     * @throws WrongPinError
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
     * @throws WrongPinError
     */
    public function createAvatarAndGetTokenByIdentityAndPassword($avatar_type, $identity, $password)
    {
        $data = $this->client->post("api/v3/login", [
            'identity'      => $identity,
            'password'      => $password,
            'create_avatar' => true,
            'avatar_type'   => $avatar_type
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
     * @throws WrongPinError
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
     * @throws WrongPinError
     */
    public function createProfileAndAvatarAndGetTokenByIdentityAndPassword($avatar_type, $name, $identity, $password)
    {
        $data = $this->client->post("api/v3/register", [
            'name'          => $name,
            'email'         => $identity,
            'password'      => $password,
            'create_avatar' => true,
            'avatar_type'   => $avatar_type
        ]);
        return $data['token'];
    }

    /**
     * @param $token
     * @return array
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    public function sendEmailVerificationLink($token)
    {
        return $this->client->get("api/v3/send-verification-link?token=$token");
    }

    /**
     * @param $token
     * @param $reason
     * @return array
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    public function logout($token, $reason)
    {
        return $this->client->setToken($token)->post("/api/v1/logout", [
            'reason' => $reason
        ]);
    }

    /**
     * @param $token
     * @param $reason
     * @return array
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    public function logoutFromAll($token, $reason)
    {
        return $this->client->setToken($token)->post("/api/v1/logout-from-all", ['reason' => $reason]);
    }

    /**
     * @param $mobile
     * @param $email
     * @param $password
     * @param $purpose
     * @return array
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    public function passwordAuthenticate($mobile, $email, $password, $purpose)
    {
        $data = [
            'password' => $password,
            'purpose'  => $purpose
        ];
        if (!empty($email)) $data['email'] = $email;
        if (!empty($mobile)) $data['mobile'] = $mobile;

        return $this->client->post("/api/v1/authenticate/password", $data);
    }

    /**
     * @param $token
     * @param $purpose
     * @return array|ResponseInterface
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    public function getAuthenticateRequests($token, $purpose)
    {
        return $this->client->setToken($token)->get("/api/v1/authenticate/password/requests?purpose=$purpose");
    }

    /**
     * @param $code
     * @return string
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    public function getTokenByShebaAccountKit($code)
    {
        $data = $this->client->post("api/v3/profile/authenticate/sheba-accountkit", ['code' => $code]);
        return $data['token'];
    }

    /**
     * @param $partner_id
     * @param $customer_id
     * @param $data
     * @param $token
     * @return array
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    public function updatePosCustomer($partner_id, $customer_id, $data, $token)
    {
        return $this->client->setToken($token)->put("/api/v1/partners/$partner_id/pos-customers/$customer_id", $data);
    }

}
