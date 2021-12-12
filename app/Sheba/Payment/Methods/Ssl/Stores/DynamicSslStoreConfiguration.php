<?php

namespace Sheba\Payment\Methods\Ssl\Stores;

class DynamicSslStoreConfiguration
{
    private $configuration;

    private $password;
    private $storeId;
    private $session_url;
    private $order_validation_url;
    private $refund_url;

    public function __construct($configuration)
    {
        $this->configuration = json_decode($configuration);
        foreach ($this->configuration as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param mixed $password
     * @return DynamicSslStoreConfiguration
     */
    public function setPassword($password): DynamicSslStoreConfiguration
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param mixed $storeId
     * @return DynamicSslStoreConfiguration
     */
    public function setStoreId($storeId): DynamicSslStoreConfiguration
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSessionUrl()
    {
        return $this->session_url;
    }

    /**
     * @return mixed
     */
    public function getOrderValidationUrl()
    {
        return $this->order_validation_url;
    }

    /**
     * @return mixed
     */
    public function getRefundUrl()
    {
        return $this->refund_url;
    }
}