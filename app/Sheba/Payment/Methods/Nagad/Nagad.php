<?php namespace Sheba\Payment\Methods\Nagad;

use App\Models\Payable;
use App\Models\Payment;
use Exception;
use Sheba\Payment\Methods\Nagad\Exception\InvalidOrderId;
use Sheba\Payment\Methods\Nagad\Exception\InvalidPaymentRef;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Statuses;
use Throwable;

class Nagad extends PaymentMethod
{
    const NAME = 'nagad';
    /** @var NagadClient $client */
    private $client;
    private $VALIDATE_URL;
    private $refId;
    /**
     * @var NagadStore
     */
    private $store;

    public function __construct()
    {
        parent::__construct();
        $this->client = app(NagadClient::class);
        $this->VALIDATE_URL = config('sheba.api_url') . "/v1/nagad/validate/";
    }

    /**
     * @param mixed $refId
     * @return Nagad
     */
    public function setRefId($refId): Nagad
    {
        $this->refId = $refId;
        return $this;
    }

    public function setStore(NagadStore $store): Nagad
    {
        $this->store = $store;
        return $this;
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws Exception
     * @throws Throwable
     */
    public function init(Payable $payable): Payment
    {
        $payment = $this->createPayment($payable, $this->store->getName());
        $payment->gateway_transaction_id = Inputs::orderID();
        $payment->update();

        try {
            $initResponse = $this->client->setStore($this->store)->init($payment->gateway_transaction_id);
            if ($initResponse->hasError()) throw new Exception($initResponse->toString());

            $resp = $this->client
                ->setStore($this->store)
                ->placeOrder($payment->gateway_transaction_id, $initResponse, $payable->amount, $this->VALIDATE_URL);

            if ($resp->hasError()) throw new Exception($resp->toString());

            $resp->setRefId($initResponse->getPaymentReferenceId());
            $payment->redirect_url = $resp->getCallbackUrl();
            $payment->transaction_details = $resp->toString();
            $payment->update();

            return $payment;
        } catch (Throwable $e) {
            $this->onInitFailed($payment, $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param \App\Models\Payment $payment
     * @param $error
     */
    private function onInitFailed(Payment $payment, $error)
    {
        $this->paymentLogRepo->setPayment($payment);
        $this->paymentLogRepo->create([
            'to' => Statuses::INITIATION_FAILED,
            'from' => $payment->status,
            'transaction_details' => $error
        ]);
        $payment->status = Statuses::INITIATION_FAILED;
        $payment->transaction_details = $error;
        $payment->update();
    }

    /**
     * @param Payment $payment
     * @return Payment
     * @throws InvalidOrderId
     */
    public function validate(Payment $payment): Payment
    {
        $res = (new Validator([], true));
        try {
            if (empty($this->refId)) throw new InvalidPaymentRef();
            $res = $this->client->setStore($this->store)->validate($this->refId);
            if ($res->getStatus()) {
                return $this->statusChanger->setPayment($payment)->changeToValidated($res->toString());
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
        }
        $this->statusChanger->setPayment($payment)->changeToValidationFailed($res->toString());
        return $payment;
    }

    public function getMethodName(): string
    {
        return self::NAME;
    }
}
