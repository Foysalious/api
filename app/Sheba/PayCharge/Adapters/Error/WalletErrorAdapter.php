<?php

namespace Sheba\PayCharge\Adapters\Error;


use Sheba\PayCharge\Methods\MethodError;

class WalletErrorAdapter implements MethodErrorAdapter
{
    private $walletError;

    public function __construct($wallet_error)
    {
        $this->walletError = $wallet_error;
    }

    public function getError(): MethodError
    {
        $method_error = new MethodError();
        $method_error->code = '';
        $method_error->message = '';
        return $method_error;
    }
}