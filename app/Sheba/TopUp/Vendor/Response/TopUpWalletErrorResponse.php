<?php

namespace Sheba\TopUp\Vendor\Response;


class TopUpWalletErrorResponse extends TopUpErrorResponse
{
    protected $errorCode = 421;
    protected $errorMessage = "Wallet exceeded.";
}