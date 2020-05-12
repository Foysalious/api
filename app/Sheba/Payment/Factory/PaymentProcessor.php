<?php namespace Sheba\Payment\Factory;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Sheba\Payment\Methods\Bkash\Bkash;
use Sheba\Payment\Methods\Cbl\Cbl;
use Sheba\Payment\Methods\Cod;
use Sheba\Payment\Methods\OkWallet\OkWallet;
use Sheba\Payment\Methods\PartnerWallet;
use Sheba\Payment\Methods\PortWallet\PortWallet;
use Sheba\Payment\Methods\Ssl\Ssl;
use Sheba\Payment\Methods\Wallet;

class PaymentProcessor
{
    private $method;

    /**
     * PaymentProcessor constructor.
     * @param $method
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
     * @return Bkash|Cbl|Ssl|Wallet|PartnerWallet|OkWallet|PortWallet
     */

    private function getMethod($method)
    {
        if (!PaymentStrategy::isValid($method)) throw new InvalidArgumentException('Invalid Method.');

        if ($method == PaymentStrategy::ONLINE) $method = PaymentStrategy::getDefaultOnlineMethod();

        switch ($method) {
            case PaymentStrategy::SSL: return app(Ssl::class);
            case PaymentStrategy::BKASH: return app(Bkash::class);
            case PaymentStrategy::WALLET: return app(Wallet::class);
            case PaymentStrategy::CBL: return app(Cbl::class);
            case PaymentStrategy::PARTNER_WALLET: return app(PartnerWallet::class);
            case PaymentStrategy::OK_WALLET: return app(OkWallet::class);
            case PaymentStrategy::SSL_DONATION: return app(Ssl::class)->setDonationConfig();
            case PaymentStrategy::PORT_WALLET: return app(PortWallet::class);
        }
    }

}
