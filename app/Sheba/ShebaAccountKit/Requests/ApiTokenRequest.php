<?php namespace Sheba\ShebaAccountKit\Requests;

class ApiTokenRequest
{
    private $appId;

    public function setAppId($api_id)
    {
        $this->appId = $api_id;
        return $this;
    }

    public function getAppId()
    {
        return $this->appId;
    }
}

