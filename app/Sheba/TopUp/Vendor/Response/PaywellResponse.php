<?php namespace Sheba\TopUp\Vendor\Response;

use Sheba\Dal\TopupOrder\Statuses;

class PaywellResponse extends TopUpResponse
{
    public function hasSuccess(): bool
    {
        return $this->response && ($this->response->status == 200 || $this->response->status == 100);
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

    public function resolveTopUpSuccessStatus()
    {
        return ($this->response->status == 100) ? Statuses::PENDING : Statuses::SUCCESSFUL;
    }

    public function isPending()
    {
        return $this->hasSuccess() && $this->response->status == 100;
    }
}