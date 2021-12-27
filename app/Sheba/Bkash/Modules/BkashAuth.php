<?php namespace Sheba\Bkash\Modules;

class BkashAuth
{
    protected $appKey;
    protected $appSecret;
    protected $username;
    protected $password;
    protected $url;
    protected $merchantNumber;

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

    /**
     * @param mixed $merchant_number
     * @return BkashAuth
     */
    public function setMerchantNumber($merchant_number)
    {
        $this->merchantNumber = $merchant_number;
        return $this;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @return mixed
     */
    public function getAppKey()
    {
        return $this->appKey;
    }

    /**
     * @return mixed
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getMerchantNumber()
    {
        return $this->merchantNumber;
    }

}