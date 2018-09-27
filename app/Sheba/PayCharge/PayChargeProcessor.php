<?php namespace Sheba\PayCharge;

use Sheba\PayCharge\Methods\Bkash;
use Sheba\PayCharge\Methods\Cbl;
use Sheba\PayCharge\Methods\Cod;
use Sheba\PayCharge\Methods\Ssl;
use Sheba\PayCharge\Methods\Wallet;

class PayChargeProcessor
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
        return in_array($method, (new \ReflectionClass(PayChargeStrategy::class))->getStaticProperties());
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