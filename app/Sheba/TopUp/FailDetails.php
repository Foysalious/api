<?php namespace Sheba\TopUp;

use Sheba\Dal\TopupOrder\FailedReason;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;
use Sheba\TopUp\Vendor\Response\TopUpErrorResponse;

class FailDetails
{
    /** @var string */
    private $reason;
    /** @var string */
    private $message;
    /** @var string */
    private $transactionDetails;

    /**
     * @param mixed $message
     * @return FailDetails
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param mixed $reason
     * @return FailDetails
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @param mixed $transactionDetails
     * @return FailDetails
     */
    public function setTransactionDetails($transactionDetails)
    {
        $this->transactionDetails = $transactionDetails;
        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getTransactionDetails()
    {
        return $this->transactionDetails;
    }

    public static function buildFromErrorResponse(TopUpErrorResponse $response)
    {
        return (new self())
            ->setMessage($response->getErrorMessage())
            ->setReason($response->getFailedReason())
            ->setTransactionDetails([
                'code' => $response->errorCode,
                'message' => $response->errorMessage,
                'response' => $response->errorResponse
            ]);
    }

    public static function buildFromIpnFailResponse(FailResponse $response)
    {
        return (new self())
            ->setReason(FailedReason::GATEWAY_ERROR)
            ->setTransactionDetails($response->getTransactionDetails());
    }
}