<?php namespace Sheba\Payment;

use Sheba\Payment\Methods\Bkash;
use Sheba\Payment\Methods\Cbl;
use Sheba\Payment\Methods\Cod;
use Sheba\Payment\Methods\Ssl;
use Sheba\Payment\Methods\Wallet;

class PaymentProcessor
{

    private $method;

    public function __construct($method)
    {
        $this->method = $this->getMethod($method);
    }

    public function method()
    {
        return $this->method;
    }

    private function isValidMethod($method)
    {
        return in_array($method, (new \ReflectionClass(PaymentStrategy::class))->getStaticProperties());
    }

    private function getMethod($method)
    {
        if (!$this->isValidMethod($method)) throw new \InvalidArgumentException('Invalid Method.');

        switch ($method) {
            case 'cod':
                return new Cod();
            case 'bkash':
                return new Bkash();
            case 'online':
                return new Ssl();
            case 'wallet':
                return new Wallet();
            case 'cbl':
                return new Cbl();
        }
    }
}