<?php namespace Sheba\TopUp\Vendor\Response;

use Sheba\TopUp\Gateway\Pretups\Pretups;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Pretups\PretupsFailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Pretups\PretupsSuccessResponse;

class PretupsResponse extends TopUpResponse
{
    public function hasSuccess(): bool
    {
        return $this->response && $this->response->TXNSTATUS == 200;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->response->TXNID;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->response->TXNID;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return isset($this->response->MESSAGE) ? $this->response->MESSAGE : 'Vendor api call error.';
    }

    public function isPending()
    {
        return false;
    }

    /**
     * @return IpnResponse
     */
    public function makeIpnResponse()
    {
        /** @var IpnResponse $ipn_response */
        $ipn_response = $this->hasSuccess() ? app(PretupsSuccessResponse::class) : app(PretupsFailResponse::class);
        $ipn_response->setResponse($this->response);
        return $ipn_response;
    }
}