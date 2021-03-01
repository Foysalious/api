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

    public function toArray()
    {
        return [
            'code' => $this->errorCode,
            'message' => $this->errorMessage,
            'response' => $this->errorResponse
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
