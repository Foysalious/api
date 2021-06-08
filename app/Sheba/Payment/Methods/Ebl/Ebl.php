<?php


namespace Sheba\Payment\Methods\Ebl;


use App\Models\Payable;
use App\Models\Payment;
use Exception;
use ReflectionException;
use Sheba\Payment\Methods\Ebl\Stores\EblStore;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\TPProxy\TPProxyServerError;

class Ebl extends PaymentMethod
{
    const NAME = 'ebl';
    private $client;
    /** @var EblStore $store */
    private $store;

    /**
     * @param EblStore $store
     * @return Ebl
     */
    public function setStore(EblStore $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws ReflectionException
     * @throws TPProxyServerError
     * @throws Exception
     */
    public function init(Payable $payable): Payment
    {
        $payment = $this->createPayment($payable);
        $input   = (new EblInputs($this->store))->setPayment($payment)->generate();

        EblClient::get()->setStore($this->store)->init($input);
        return $payment;
    }

    public function validate(Payment $payment): Payment
    {
        // TODO: Implement validate() method.
    }

    public function getMethodName()
    {
        return self::NAME;
    }
}
