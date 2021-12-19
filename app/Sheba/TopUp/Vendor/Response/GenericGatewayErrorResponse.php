<?php namespace Sheba\TopUp\Vendor\Response;

class GenericGatewayErrorResponse extends TopUpResponse
{
    /**
     * @return bool
     */
    public function hasSuccess(): bool
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return "";
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return "";
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return 'Error message not given.';
    }

    public function isPending()
    {
        return false;
    }
}