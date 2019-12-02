<?php namespace Sheba\ShebaAccountKit\Requests;


class OtpSendRequest
{
    private $mobile;
    private $appId;
    private $apiToken;

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return OtpSendRequest
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param mixed $appId
     * @return OtpSendRequest
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param mixed $apiToken
     * @return OtpSendRequest
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
        return $this;
    }


}