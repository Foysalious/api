<?php namespace Sheba\TopUp\Vendor\Response;


class SslResponse extends TopUpResponse
{
    public function hasSuccess(): bool
    {
        return $this->response && $this->response->recharge_status == 200;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->response->guid;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->response->recharge_status;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->response->Message;
    }

    public function isPending()
    {
        return $this->hasSuccess();
    }
}
