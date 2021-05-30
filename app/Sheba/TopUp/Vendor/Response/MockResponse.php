<?php namespace Sheba\TopUp\Vendor\Response;


class MockResponse extends TopUpResponse
{
    /**
     * @return bool
     */
    public function hasSuccess(): bool
    {
        return $this->response->TXNSTATUS == 200;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->response->TXNID;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->response->TXNID;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return isset($this->response->MESSAGE) ? $this->response->MESSAGE : 'Error message not given.';
    }

    public function isPending()
    {
        return false;
    }
}