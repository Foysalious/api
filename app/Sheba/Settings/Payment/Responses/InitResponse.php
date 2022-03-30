<?php namespace Sheba\Settings\Payment\Responses;


class InitResponse
{
    private $transactionId;
    private $redirectUrl;

    public function setTransactionId($id): InitResponse
    {
        $this->transactionId = $id;
        return $this;
    }

    public function setRedirectUrl($url): InitResponse
    {
        $this->redirectUrl = $url;
        return $this;
    }

    public function isSuccess(): bool
    {
        return isset($this->transactionId);
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @return mixed
     */
    public function getSuccessUrl()
    {
        return $this->successUrl;
    }

}