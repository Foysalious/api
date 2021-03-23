<?php namespace Sheba\TopUp\Vendor\Response;

use Sheba\Dal\TopupOrder\FailedReason;

class TopUpSystemErrorResponse extends TopUpErrorResponse
{
    protected $errorCode = 500;
    protected $errorMessage = "Something went wrong.";
    protected $failedReason = FailedReason::UNKNOWN;

    public function setFailedReason($reason)
    {
        return $this;
    }
}