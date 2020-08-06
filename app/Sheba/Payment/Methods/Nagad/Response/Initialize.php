<?php namespace Sheba\Payment\Methods\Nagad\Response;

class Initialize extends Response
{
    protected $shouldDecode = true;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getPaymentReferenceId()
    {
        return isset($this->output['paymentReferenceId']) ? $this->output['paymentReferenceId'] : null;
    }

    public function getChallenge()
    {
        return isset($this->output['challenge']) ? $this->output['challenge'] : null;
    }


}
