<?php namespace Sheba\Bkash\Modules;


class BkashAuth
{
    private $appKey;
    private $appSecret;
    private $username;
    private $password;
    private $url;

    public function setKey($key)
    {
        $this->appKey = $key;
        return $this;
    }

    public function setSecret($secret)
    {
        $this->appSecret = $secret;
        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}