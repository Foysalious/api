<?php namespace Sheba\Payment\Methods\Nagad\Response;

use Illuminate\Support\Facades\Log;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;

class CheckoutComplete extends Response
{
    protected $shouldDecode = false;

    public function __construct($data, NagadStore $store)
    {
        Log::info(json_encode($data));
        parent::__construct($data, $store);
    }

    public function getCallbackUrl()
    {
        return $this->data["callBackUrl"] ?? null;
    }
}
