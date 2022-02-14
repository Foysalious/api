<?php namespace Sheba\Payment\Methods\Ssl\Stores;

abstract class SslStore
{
    protected $storeId;
    protected $storePassword;
    protected $sessionUrl;
    protected $orderValidationUrl;

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    public function getStoreId()
    {
        return trim($this->storeId);
    }

    /**
     * @return string
     */
    public function getStorePassword()
    {
        return trim($this->storePassword);
    }

    /**
     * @return string
     */
    public function getSessionUrl()
    {
        return $this->sessionUrl;
    }

    /**
     * @return string
     */
    public function getOrderValidationUrl()
    {
        return $this->orderValidationUrl;
    }

    /**
     * @return string
     */
    public function getRefundUrl()
    {
        return config("payment.ssl.urls.refund");
    }
}
