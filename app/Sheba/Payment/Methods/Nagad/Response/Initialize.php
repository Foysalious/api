<?php namespace Sheba\Payment\Methods\Nagad\Response;

use Sheba\Payment\Methods\Nagad\Stores\NagadStore;

class Initialize extends Response
{
    protected $shouldDecode = true;

    public function __construct($data, NagadStore $store)
    {
        parent::__construct($data, $store);
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
