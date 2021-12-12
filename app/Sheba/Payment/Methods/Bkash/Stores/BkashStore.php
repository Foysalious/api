<?php

namespace Sheba\Payment\Methods\Bkash\Stores;

use App\Models\Payable;
use App\Models\Payment;
use Sheba\Bkash\Modules\BkashAuth;

abstract class BkashStore
{
    /** @var BkashAuth */
    protected $auth;
    /** @var Payment */
    protected $payment;
    abstract function getName():string;
    public function getAuth(): BkashAuth
    {
        return $this->auth;
    }

    /**
     * @return Payment
     */
    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     * @return BkashStore
     */
    public function setPayment(Payment $payment): BkashStore
    {
        $this->payment = $payment;
        return $this;
    }

}