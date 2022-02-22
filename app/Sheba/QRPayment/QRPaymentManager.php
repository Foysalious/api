<?php

namespace App\Sheba\QRPayment;

use App\Models\Payable;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\QRPayment\Model as QRPaymentModel;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\Payment\Statuses;
use Throwable;

class QRPaymentManager
{
    /*** @var Payable */
    private $payable;
    /*** @var QRPaymentModel */
    private $qr_payment;

    private $method;

    /**
     * @param mixed $qr_payment
     * @return QRPaymentManager
     */
    public function setQrPayment(QRPaymentModel $qr_payment): QRPaymentManager
    {
        $this->qr_payment = $qr_payment;
        return $this;
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
            if ($this->qr_payment->canComplete()) {
                $completion_class = $this->payable->getQRCompletionClass();
                $completion_class->setQrPayment($this->qr_payment)->setMethod($this->method);
                $payment = $completion_class->setPayable($this->payable)->complete();
                $this->completePayment();
            }
            $this->unsetRunningCompletion();
            return $payment ? : $this->qr_payment;
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
        return 'QR_Payment::Completing::' . $this->qr_payment->id;
    }

    private function completePayment()
    {
        $this->qr_payment->reload();

        if($this->qr_payment->status !== Statuses::COMPLETED) {
            $this->qr_payment->status = Statuses::COMPLETED;
            $this->qr_payment->save();
        }
    }
}