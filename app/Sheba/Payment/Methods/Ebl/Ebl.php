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
        return $this->setPayment($this->createPayment($payable,$this->store->getName()));

    }

    private function setPayment(Payment $payment)
    {
        $payment->gateway_transaction_id = uniqid('EBL_' . $payment->id . '_');
        $payment->redirect_url           = config('sheba.ebl_url') . '/checkout?transaction_id=' . $payment->transaction_id;
        $payment->update();
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
