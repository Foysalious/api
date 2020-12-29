<?php namespace Sheba\TopUp\Vendor\Response;

use Sheba\TopUp\Gateway\Pretups\Pretups;

class PretupsResponse extends TopUpResponse
{
    public function hasSuccess(): bool
    {
        return $this->response && $this->response->TXNSTATUS == 200;
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
        return isset($this->response->MESSAGE) ? $this->response->MESSAGE : 'Vendor api call error.';
    }

    public function resolveTopUpSuccessStatus()
    {
        return Pretups::getInitialStatusStatically();
    }
}