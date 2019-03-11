<?php namespace Sheba\Bkash\Modules;

abstract class BkashModule
{
    /** @var $bkashAuth BkashAuth */
    protected $bkashAuth;

    abstract public function setBkashAuth();

    abstract public function getToken();

    abstract public function getMethod($enum);

}