<?php namespace Sheba\Settings\Payment\Responses;


class InitResponse
{
    private $successUrl;
    private $transactionId;
    private $redirectUrl;

    public function __get($name)
    {
        return $this->$name;
    }

    public function setTransactionId($id)
    {
        $this->transactionId = $id;
        return $this;
    }

    public function setSuccessUrl($url)
    {
        $this->successUrl = $url;
        return $this;
    }

    public function setRedirectUrl($url)
    {
        $this->redirectUrl = $url;
        return $this;
    }
}