<?php namespace Sheba\TopUp\Vendor\Response;

class PaywellResponse extends TopUpResponse
{
    public function hasSuccess(): bool
    {
        return $this->response && $this->response->status == 200;
    }

    /**setResponse
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->response->trans_id;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->response->status;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->response->message;
    }
}