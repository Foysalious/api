<?php namespace Sheba\Payment\Methods\Nagad\Response;

use Illuminate\Support\Facades\Log;
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
        return $this->output['paymentReferenceId'] ?? null;
    }

    public function getChallenge()
    {
        return $this->output['challenge'] ?? null;
    }
}
