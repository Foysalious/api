<?php

namespace Sheba\PayCharge\Adapters\Error;


use Sheba\PayCharge\Methods\PayChargeMethodError;

class WalletErrorAdapter implements MethodErrorAdapter
{
    private $walletError;

    public function __construct($wallet_error)
    {
        $this->walletError = $wallet_error;
    }

    public function getError(): PayChargeMethodError
    {
        $method_error = new PayChargeMethodError();
        $method_error->code = '';
        $method_error->message = '';
        return $method_error;
    }
}