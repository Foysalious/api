<?php namespace Sheba\TopUp\Vendor\Response;


use Sheba\Dal\TopupOrder\FailedReason;

class TopUpWalletErrorResponse extends TopUpErrorResponse
{
    protected $errorCode = 421;
    protected $errorMessage = "Wallet exceeded.";
    protected $failedReason = FailedReason::INSUFFICIENT_BALANCE;

    public function setFailedReason($reason)
    {
        return $this;
    }
}