<?php namespace Sheba\Payment\Methods\Nagad;


use App\Models\Payable;
use App\Models\Payment;
use Exception;
use Sheba\Payment\Methods\PaymentMethod;

class Nagad extends PaymentMethod
{
    const NAME = 'nagad';
    /** @var NagadClient $client */
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = app(NagadClient::class);
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws Exception
     */
    public function init(Payable $payable): Payment
    {
        $payment                         = $this->createPayment($payable);
        $payment->gateway_transaction_id = Inputs::orderID();
        $payment->save();
        $initResponse = $this->client->init($payment->gateway_transaction_id);
        dd($initResponse);
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
