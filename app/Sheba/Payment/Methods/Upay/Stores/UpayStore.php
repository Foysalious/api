<?php

namespace Sheba\Payment\Methods\Upay\Stores;

abstract class UpayStore
{

    /**
     * @var UpayStoreConfig
     */
    protected $config;

    /**
     * @return UpayStoreConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    abstract public function getName();

}