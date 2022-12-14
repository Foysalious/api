<?php namespace Sheba\TopUp\Vendor\Response;

use Sheba\TopUp\Gateway\PayStation;

class PayStationResponse extends TopUpResponse
{
    /**
     * @inheritDoc
     */
    public function hasSuccess(): bool
    {
        return $this->isCompleted() || $this->isPending();
    }

    public function isCompleted(): bool
    {
        return $this->response && $this->response->Status == "SUCCESS";
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId()
    {
        return $this->response->Transiction_id;
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return $this->response->Message;
    }

    /**
     * @inheritDoc
     */
    public function resolveTopUpSuccessStatus(): string
    {
        return PayStation::getInitialStatusStatically();
    }

    public function isPending(): bool
    {
        return $this->response && $this->response->Status == "REQUEST ACCEPTED";
    }
}