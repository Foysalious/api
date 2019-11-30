<?php namespace Sheba\ShebaAccountKit\Requests;


class OtpValidateRequest
{
    private $otp;
    private $appId;
    private $apiToken;

    /**
     * @return mixed
     */
    public function getOtp()
    {
        return $this->otp;
    }

    /**
     * @param mixed $otp
     * @return OtpValidateRequest
     */
    public function setOtp($otp)
    {
        $this->otp = $otp;
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
     * @return OtpValidateRequest
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
     * @return OtpValidateRequest
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
        return $this;
    }

}