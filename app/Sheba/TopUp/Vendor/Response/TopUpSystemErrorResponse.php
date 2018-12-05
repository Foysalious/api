<?php

namespace Sheba\TopUp\Vendor\Response;


class TopUpSystemErrorResponse extends TopUpErrorResponse
{
    protected $errorCode = 500;
    protected $errorMessage = "Something went wrong.";
}