<?php namespace Sheba\Bkash\Modules;

abstract class BkashModule
{
    protected $appKey;
    protected $appSecret;
    protected $username;
    protected $password;
    protected $url;
    /** @var $bkashAuth BkashAuth */
    protected $bkashAuth;

    abstract public function setBkashAuth();

    abstract public function getToken();

    abstract public function getMethod($enum);

}