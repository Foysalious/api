<?php namespace Sheba\Payment\Methods\Nagad\Stores;

abstract class NagadStore
{
    protected $merchantId;
    protected $publicKey;
    protected $privateKey;
    protected $contextPath;
    protected $baseUrl;

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function getContextPath()
    {
        return $this->contextPath;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
