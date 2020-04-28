<?php namespace Sheba\Payment\Methods\PortWallet;

use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\ShebaPayment;

class PortWallet extends PaymentMethod
{
    CONST NAME = 'port_wallet';

    /** @var Service */
    private $portWallet;

    public function __construct(Service $port_wallet)
    {
        parent::__construct();
        $this->portWallet = $port_wallet;
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws \Exception
     */
    public function init(Payable $payable): Payment
    {
        $payment = $this->createPayment($payable);
        $init_response = $this->portWallet->setPayment($payment)->createInvoice();
        $this->statusChanger->setPayment($payment);

        if ($init_response->hasSuccess()) {
            $success = $init_response->getSuccess();
            $payment->transaction_details = json_encode($success->details);
            $payment->gateway_transaction_id = $success->id;
            $payment->redirect_url = $success->redirect_url;
            $payment->update();
        } else {
            $this->statusChanger->changeToInitiationFailed($init_response->getErrorDetailsString());

            /** @var ShebaPayment $sheba_payment */
            $sheba_payment = app(ShebaPayment::class);
            $payment = $sheba_payment->setMethod("ssl")->init($payable);
        }
        return $payment;
    }

    public function validate(Payment $payment)
    {
        $validation_response = $this->portWallet->setPayment($payment)->validate();
        $this->statusChanger->setPayment($payment);
        if ($validation_response->hasSuccess()) {
            $payment = $this->statusChanger->changeToValidated($validation_response->getSuccessDetailsString());
        } else {
            $payment = $this->statusChanger->changeToValidationFailed($validation_response->getErrorDetailsString());
        }
        return $payment;
    }

    public function getMethodName()
    {
        return self::NAME;
    }
}
