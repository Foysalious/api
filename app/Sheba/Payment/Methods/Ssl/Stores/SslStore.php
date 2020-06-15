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
        return $this->storeId;
    }

    /**
     * @return string
     */
    public function getStorePassword()
    {
        return $this->storePassword;
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
}
