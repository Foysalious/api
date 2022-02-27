<?php

namespace App\Sheba\QRPayment;

use App\Models\Payable;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\QRPayment\Model as QRPaymentModel;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\Statuses;
use Throwable;

class QRPaymentManager extends PaymentManager
{
    /*** @var Payable */
    private $payable;
    /*** @var QRPaymentModel */
    private $qrPayment;

    /**
     * @param mixed $qr_payment
     * @return QRPaymentManager
     */
    public function setQrPayment(QRPaymentModel $qr_payment): QRPaymentManager
    {
        $this->qrPayment = $qr_payment;
        return $this->setPayable($this->qrPayment->payable);
    }

    /**
     * @param mixed $payable
     * @return QRPaymentManager
     */
    public function setPayable(Payable $payable): QRPaymentManager
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @param mixed $method
     * @return QRPaymentManager
     */
    public function setMethod($method): QRPaymentManager
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @throws Throwable
     * @throws AlreadyCompletingPayment
     */
    public function complete()
    {
        $this->runningCompletionCheckAndSet();
        try {
            if (!$this->qrPayment->canComplete()) return $this->qrPayment;
            if($this->qrPayment->completion_type) {
                $completion_class = $this->payable->getCompletionClass();
                $payment = $completion_class->setQrPayment($this->qrPayment)->setMethod($this->qrPayment->qrGateway->method_name)->complete();
            }
            $this->completePayment();
            $this->unsetRunningCompletion();
            return $payment ?? $this->qrPayment;
        } catch (Throwable $e) {
            $this->unsetRunningCompletion();
            throw $e;
        }
    }

    /**
     * @throws AlreadyCompletingPayment
     */
    private function runningCompletionCheckAndSet()
    {
        $key = $this->getKey();
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

    private function getKey()
    {
        return 'QR_Payment::Completing::' . $this->qrPayment->id;
    }

    private function completePayment()
    {
        $this->qrPayment->reload();

        if ($this->qrPayment->status !== Statuses::COMPLETED) {
            $this->qrPayment->status = Statuses::COMPLETED;
            $this->qrPayment->save();
        }
    }
}