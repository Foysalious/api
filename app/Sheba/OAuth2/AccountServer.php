<?php namespace Sheba\OAuth2;


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
        return $this->getTokenByIdAndRememberToken($avatar->remember_token, $avatar->id, $type);
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
}
