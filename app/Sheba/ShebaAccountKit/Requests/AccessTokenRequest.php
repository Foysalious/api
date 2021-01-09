<?php namespace Sheba\ShebaAccountKit\Requests;

class AccessTokenRequest
{
    private $authorizationCode;
    
    /**
     * @return mixed
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * @param mixed $authorizationCode
     * @return AccessTokenRequest
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;
        return $this;
    }
}
