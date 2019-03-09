<?php namespace Sheba\Settings\Payment\Responses;


class InitResponse
{
    private $successUrl;

    public function __get($name)
    {
        return $this->name;
    }

    public function setSuccessUrl($url)
    {
        $this->successUrl = $url;
        return $this;
    }
}