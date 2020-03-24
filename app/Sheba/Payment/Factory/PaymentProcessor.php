<?php namespace Sheba\Payment\Factory;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Sheba\Payment\Methods\Bkash\Bkash;
use Sheba\Payment\Methods\Cbl\Cbl;
use Sheba\Payment\Methods\Cod;
use Sheba\Payment\Methods\OkWallet\OkWallet;
use Sheba\Payment\Methods\PartnerWallet;
use Sheba\Payment\Methods\Ssl\Ssl;
use Sheba\Payment\Methods\Wallet;

class PaymentProcessor {
    private $method;

    /**
     * PaymentProcessor constructor.
     * @param $method
     * @throws ReflectionException
     */
    public function __construct($method) {
        $this->method = $this->getMethod($method);
    }


    public function method() {
        return $this->method;
    }

    /**
     * @param $method
     * @return bool
     * @throws ReflectionException
     */
    private function isValidMethod($method) {
        return in_array($method, (new ReflectionClass(PaymentStrategy::class))->getStaticProperties());
    }


    /**
     * @param $method
     * @return Bkash|Cbl|Cod|Ssl|Wallet|PartnerWallet|OkWallet
     * @throws ReflectionException
     */

    private function getMethod($method) {
        if (!$this->isValidMethod($method)) throw new InvalidArgumentException('Invalid Method.');

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
            case 'partner_wallet':
                return new PartnerWallet();

            case 'ssl_donation':
                return (new Ssl())->setDonationConfig();
        }
    }

}
