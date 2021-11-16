<?php

namespace Sheba\Payment\Methods;

class DynamicStoreConfiguration
{
    private $configuration;

    public function __construct($configuration)
    {
        $this->configuration = json_decode($configuration);
    }

    public function getPassword()
    {
        return $this->configuration->password;
    }

}