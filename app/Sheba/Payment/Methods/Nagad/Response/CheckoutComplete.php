<?php


namespace Sheba\Payment\Methods\Nagad\Response;

class CheckoutComplete extends Response
{
    protected $shouldDecode = false;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getCallbackUrl()
    {
        return isset($this->data["callBackUrl"]) ? $this->data["callBackUrl"] : null;
    }

}
