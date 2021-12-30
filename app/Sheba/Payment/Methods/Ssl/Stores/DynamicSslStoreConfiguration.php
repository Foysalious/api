<?php

namespace Sheba\Payment\Methods\Ssl\Stores;

use Sheba\ResellerPayment\EncryptionAndDecryption;

class DynamicSslStoreConfiguration
{
    protected $configuration;

    private $password;
    private $storeId;
    private $session_url;
    private $order_validation_url;
    private $refund_url;

    public function __construct($configuration = "")
    {
        $configuration = (new EncryptionAndDecryption())->setData($configuration)->getDecryptedData();
        $this->configuration = json_decode($configuration);
        if(isset($this->configuration))
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

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function toArray(): array
    {
        return [
            "storeId" => $this->storeId,
            "password" => $this->password,
            "session_url" => $this->session_url,
            "order_validation_url" => $this->order_validation_url,
            "refund_url" => $this->refund_url,
        ];
    }

    /**
     * @param mixed $session_url
     * @return DynamicSslStoreConfiguration
     */
    public function setSessionUrl($session_url): DynamicSslStoreConfiguration
    {
        $this->session_url = $session_url;
        return $this;
    }

    /**
     * @param mixed $order_validation_url
     * @return DynamicSslStoreConfiguration
     */
    public function setOrderValidationUrl($order_validation_url): DynamicSslStoreConfiguration
    {
        $this->order_validation_url = $order_validation_url;
        return $this;
    }

    /**
     * @param mixed $refund_url
     * @return DynamicSslStoreConfiguration
     */
    public function setRefundUrl($refund_url): DynamicSslStoreConfiguration
    {
        $this->refund_url = $refund_url;
        return $this;
    }
}