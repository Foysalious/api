<?php namespace Sheba\Payment\Methods\Response;

use App\Models\Payment;

abstract class PaymentMethodResponse
{
    protected $response;
    protected $payment;

    /**
     * @param $response
     * @return PaymentMethodResponse
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param Payment $payment
     * @return PaymentMethodResponse
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    abstract public function hasSuccess();

    abstract public function getSuccess(): PaymentMethodSuccessResponse;

    abstract public function getError(): PaymentMethodErrorResponse;

    /**
     * @return string
     */
    public function getErrorDetailsString()
    {
        $error = $this->getError();
        return json_encode($error->details);
    }

    /**
     * @return string
     */
    public function getSuccessDetailsString()
    {
        $success = $this->getSuccess();
        return json_encode($success->details);
    }
}
