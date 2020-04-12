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

class PaymentProcessor
{
    private $method;

    /**
     * PaymentProcessor constructor.
     * @param $method
     * @throws ReflectionException
     */
    public function __construct($method)
    {
        $this->method = $this->getMethod($method);
    }


    public function method()
    {
        return $this->method;
    }

    /**
     * @param $method
     * @return bool
     * @throws ReflectionException
     */
    private function isValidMethod($method)
    {
        return in_array($method, (new ReflectionClass(PaymentStrategy::class))->getStaticProperties());
    }


    /**
     * @param $method
     * @return Bkash|Cbl|Cod|Ssl|Wallet|PartnerWallet|OkWallet
     * @throws ReflectionException
     */

    private function getMethod($method)
    {
        if (!$this->isValidMethod($method)) throw new InvalidArgumentException('Invalid Method.');

        switch ($method) {
            case 'cod':
                return app(Cod::class);
            case 'bkash':
                return app(Bkash::class);
            case 'online':
                return app(Ssl::class);
            case 'wallet':
                return app(Wallet::class);
            case 'cbl':
                return app(Cbl::class);
            case 'partner_wallet':
                return app(PartnerWallet::class);
            case 'ok_wallet':
                return app(OkWallet::class);
            case 'ssl_donation':
                return app(Ssl::class)->setDonationConfig();
        }
    }

}
