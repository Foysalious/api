<?php


namespace Sheba\TopUp\Vendor\Response;


use Sheba\TopUp\Gateway\BdRecharge;

class BdRechargeResponse extends TopUpResponse
{

    /**
     * @inheritDoc
     */
    public function hasSuccess(): bool
    {
        return $this->response && $this->response->status == 200;
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId()
    {
        return $this->response->data->tid;
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode()
    {
        return $this->response->status;
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return $this->response->data->message;
    }

    /**
     * @inheritDoc
     */
    public function resolveTopUpSuccessStatus()
    {
        return BdRecharge::getInitialStatusStatically();
    }

    public function isPending()
    {
        return $this->hasSuccess();
    }
}