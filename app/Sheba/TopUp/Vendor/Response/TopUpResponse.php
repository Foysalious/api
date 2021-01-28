<?php namespace Sheba\TopUp\Vendor\Response;

use Exception;
use Sheba\Dal\TopupOrder\FailedReason;

abstract class TopUpResponse
{
    protected $response;
    /** @var TopUpErrorResponse */
    protected $errorResponse;

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function setErrorResponse(TopUpErrorResponse $error)
    {
        $this->errorResponse = $error;
        return $this;
    }

    /**
     * @return bool
     */
    abstract public function hasSuccess(): bool;

    /**
     * @return mixed
     */
    abstract public function getTransactionId();

    /**
     * @return mixed
     */
    abstract public function getErrorCode();

    /**
     * @return string
     */
    abstract public function getErrorMessage();

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return !$this->hasSuccess();
    }

    /**
     * Success status can be different for different vendor response.
     *
     * @return string
     */
    abstract public function resolveTopUpSuccessStatus();

    /**
     * @return TopUpSuccessResponse
     * @throws Exception
     */
    public function getSuccess(): TopUpSuccessResponse
    {
        if (!$this->hasSuccess()) throw new Exception('Response does not have success.');

        $topup_response = new TopUpSuccessResponse();
        $topup_response->transactionId = $this->getTransactionId();
        $topup_response->transactionDetails = $this->response;
        $topup_response->topUpStatus = $this->resolveTopUpSuccessStatus();
        return $topup_response;
    }

    /**
     * @return TopUpErrorResponse
     * @throws Exception
     */
    public function getErrorResponse(): TopUpErrorResponse
    {
        if ($this->errorResponse) return $this->errorResponse;

        if ($this->hasSuccess()) throw new Exception('Response has success.');

        $topup_error = new TopUpErrorResponse();
        $topup_error->errorCode = isset($this->response->recharge_status) ? $this->response->recharge_status : 400;
        $topup_error->errorMessage = isset($this->response->Message) ? $this->response->Message : 'Vendor api call error';
        $topup_error->errorResponse = $this->response ? $this->response : '';
        $topup_error->setFailedReason(FailedReason::GATEWAY_ERROR);
        return $topup_error;
    }
}
