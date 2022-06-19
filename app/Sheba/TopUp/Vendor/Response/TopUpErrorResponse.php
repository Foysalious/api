<?php namespace Sheba\TopUp\Vendor\Response;


use Sheba\Dal\TopupOrder\FailedReason;

class TopUpErrorResponse
{
    protected $errorCode;
    protected $errorMessage;
    protected $errorResponse;
    protected $failedReason;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function setFailedReason($reason)
    {
        if (FailedReason::isInvalid($reason)) $reason = "";
        $this->failedReason = $reason;
        return $this;
    }
    
    public function getFailedReason()
    {
        return $this->failedReason;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getErrorCode()
    {
        return $this->errorMessage;
    }

    public function getErrorResponse()
    {
        return $this->errorResponse;
    }
}
