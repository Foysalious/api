<?php namespace Sheba\Payment;

use App\Models\Payable;
use App\Models\Payment;
use Illuminate\Support\Facades\Redis;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Policy\PaymentInitiate;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Methods\PaymentMethod;
use Throwable;

class PaymentManager
{
    /** @var string */
    private $methodName;
    /** @var Payable */
    private $payable;
    /** @var Payment */
    private $payment;

    /** @var PaymentMethod */
    private $method;

    /**
     * @param $name
     * @return $this
     */
    public function setMethodName($name)
    {
        $this->methodName = $name;
        return $this;
    }

    /**
     * @param Payable $payable
     * @return $this
     */
    public function setPayable(Payable $payable)
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @param Payment $payment
     * @return $this
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        $this->setPayable($payment->payable);
        return $this;
    }

    /**
     * @return PaymentMethod
     * @throws InvalidPaymentMethod
     */
    public function getMethod()
    {
        if ($this->method) return $this->method;
        $this->method = PaymentStrategy::getMethod($this->methodName, $this->payable);
        return $this->method;
    }

    /**
     * @return bool true if can init
     * @throws InitiateFailedException otherwise
     * @throws InvalidPaymentMethod
     */
    private function canInit()
    {
        /** @var PaymentInitiate $payment_initiate */
        $payment_initiate = app(PaymentInitiate::class);
        return $payment_initiate->setPaymentMethod($this->getMethod())->setPayable($this->payable)->canPossible();
    }

    /**
     * @return Payment
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function init()
    {
        $this->canInit();
        $payment = $this->getMethod()->init($this->payable);
        if (!$payment->isInitiated()) throw new InitiateFailedException();
        return $payment;
    }

    /**
     * @return Payment
     * @throws InvalidPaymentMethod
     */
    public function validate()
    {
        return $this->getMethod()->validate($this->payment);
    }

    /**
     * @return $this
     */
    public function storeRequestPayload()
    {
        $this->payment->request_payload = json_encode(request()->all());
        $this->payment->save();
        return $this;
    }

    /**
     * @return Payment
     * @throws InvalidPaymentMethod|AlreadyCompletingPayment|Throwable
     */
    public function complete()
    {
        $this->runningCompletionCheckAndSet();
        try {
            $payment = $this->storeRequestPayload()->validate();
            if ($payment->canComplete()) {
                $completion_class = $this->payable->getCompletionClass();
                $completion_class->setPayment($payment);
                $payment = $completion_class->complete();
            }
            $this->unsetRunningCompletion();
            return $payment;
        } catch (Throwable $e) {
            $this->unsetRunningCompletion();
            throw  $e;
        }

    }

    private function getKey()
    {
        return 'Payment::Completing::' . $this->payment->id;
    }

    /**
     * @throws AlreadyCompletingPayment
     */
    private function runningCompletionCheckAndSet()
    {
        $key     = $this->getKey();
        $already = Redis::get($key);
        if ($already) {
            throw new AlreadyCompletingPayment();
        }
        Redis::set($key, 1);
    }

    private function unsetRunningCompletion()
    {
        Redis::del($this->getKey());
    }
}
