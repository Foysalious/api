<?php namespace Sheba\CancelRequest;


class CancelRequestFactory
{
    private $requestedBy;


    public function setRequestedBy($requestedBy)
    {
        $this->requestedBy = $requestedBy;
        return $this;
    }

    /**
     * @return Requestor
     */
    public function get()
    {
        return $this->requestedBy == RequestedByType::USER ? app(CmRequestor::class) : app(PartnerRequestor::class);
    }

}