<?php namespace Sheba\Bkash\Modules;

abstract class BkashModule
{
    /** @var $token BkashToken */
    protected $token;
    /** @var $bkashAuth BkashAuth */
    protected $bkashAuth;

    abstract public function setBkashAuth();

    abstract public function getToken();

    abstract protected function setToken();

    abstract public function getMethod($enum);

}