<?php namespace Sheba\Payment\Complete;

use App\Models\Payable;
use App\Models\Payment;
use App\Repositories\PaymentStatusChangeLogRepository;
use Sheba\ModificationFields;
use Sheba\Payment\Statuses;
use Sheba\Dal\QRPayment\Model as QRPaymentModel;

abstract class PaymentComplete
{
    use ModificationFields;

    /** @var Payment $partner_order_payment */
    protected $payment;
    /** @var  QRPaymentModel $qrPayment */
    protected $qrPayment;

    /** @var PaymentStatusChangeLogRepository */
    protected $paymentRepository;

    protected $method;

    public function __construct()
    {
        $this->paymentRepository = app(PaymentStatusChangeLogRepository::class);
    }

    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        $this->paymentRepository->setPayment($payment);
    }

    /**
     * @param QRPaymentModel $qrPayment
     * @return PaymentComplete
     */
    public function setQrPayment(QRPaymentModel $qrPayment): PaymentComplete
    {
        $this->qrPayment = $qrPayment;
        return $this;
    }

    /**
     * @param mixed $method
     * @return PaymentComplete
     */
    public function setMethod($method): PaymentComplete
    {
        $this->method = $method;
        return $this;
    }

    protected function failPayment()
    {
        $this->changePaymentStatus(Statuses::FAILED);
    }

    protected function changePaymentStatus($to_status)
    {
        $this->paymentRepository->create(['to' => $to_status, 'from' => $this->payment->status, 'transaction_details' => $this->payment->transaction_details]);
        $this->payment->status = $to_status;
        $this->payment->update();
    }

    protected function completePayment()
    {
        $this->changePaymentStatus(Statuses::COMPLETED);
    }

    abstract protected function saveInvoice();

    abstract public function complete();

    public function getPayable(): Payable
    {
        if ($this->qrPayment) return $this->qrPayment->payable;
        return $this->payment->payable;
    }

    public function isComplete(): bool
    {
        if ($this->qrPayment) {
            $this->qrPayment->reload();
            return $this->qrPayment->isComplete();
        }
        $this->payment->reload();
        return $this->payment->isComplete();
    }

    public function getPayment()
    {
        return $this->qrPayment ?? $this->payment;
    }
}
