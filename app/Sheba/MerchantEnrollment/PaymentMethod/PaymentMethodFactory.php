<?php

namespace Sheba\MerchantEnrollment\PaymentMethod;

use Sheba\Dal\PgwStore\Model as PgwStore;
use Sheba\MerchantEnrollment\Statics\PaymentMethodStatics;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class PaymentMethodFactory
{
    /*** @var PgwStore */
    private $payment_gateway;
    private $partner;

    /**
     * @param $payment_gateway
     * @return PaymentMethodFactory
     */
    public function setPaymentGateway($payment_gateway): PaymentMethodFactory
    {
        $this->payment_gateway = $payment_gateway;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return PaymentMethodFactory
     */
    public function setPartner($partner): PaymentMethodFactory
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return PaymentMethod
     * @throws InvalidKeyException
     */
    public function get(): PaymentMethod
    {
        $class_map = PaymentMethodStatics::classMap();
        $paymentMethodClassPath = "Sheba\\MerchantEnrollment\\PaymentMethod\\";
        $key = $this->payment_gateway->key;
        if (isset($class_map[$key])) {
            $class=$class_map[$key];
            /** @var PaymentMethod $payment_method */
            $payment_method = app("$paymentMethodClassPath$class\\$class");
            return $payment_method->setPartner($this->partner);
        }

        throw new InvalidKeyException();
    }

}