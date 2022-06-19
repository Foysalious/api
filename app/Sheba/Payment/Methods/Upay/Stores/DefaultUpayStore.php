<?php

namespace Sheba\Payment\Methods\Upay\Stores;

class DefaultUpayStore extends UpayStore
{
    const NAME = 'default';

    public function __construct()
    {
        $this->setConfigFromFile();
    }

    public function getName()
    {
        return self::NAME;
    }

    private function setConfigFromFile()
    {
        $this->config = (new UpayStoreConfig())->getFromConfig($this->getName());
    }
}