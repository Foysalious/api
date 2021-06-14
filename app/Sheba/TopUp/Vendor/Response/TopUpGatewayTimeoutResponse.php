<?php namespace Sheba\TopUp\Vendor\Response;

use Sheba\Dal\TopupOrder\FailedReason;

class TopUpGatewayTimeoutResponse extends TopUpErrorResponse
{
    protected $errorCode = 502;
    protected $errorMessage = "Gateway timeout.";
    protected $failedReason = FailedReason::GATEWAY_TIMEOUT;

    public function setFailedReason($reason)
    {
        return $this;
    }
}
