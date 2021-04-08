<?php


namespace Sheba\TopUp\Vendor\Response;


class BdRechargeResponse extends TopUpResponse
{

    /**
     * @inheritDoc
     */
    public function hasSuccess(): bool
    {
        return $this->response['status'] == 200;
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId()
    {
        return $this->response['data']['tid'];
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode()
    {
        return $this->response['status'];
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return $this->response['data']['message'];
    }

    /**
     * @inheritDoc
     */
    public function resolveTopUpSuccessStatus()
    {
        // TODO: Implement resolveTopUpSuccessStatus() method.
    }
}