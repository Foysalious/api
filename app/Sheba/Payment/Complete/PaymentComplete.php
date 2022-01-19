<?php namespace Sheba\Payment\Complete;

use App\Models\Payment;
use App\Repositories\PaymentStatusChangeLogRepository;
use Sheba\ModificationFields;
use Sheba\Payment\Statuses;

abstract class PaymentComplete
{
    use ModificationFields;
    /** @var Payment $partner_order_payment */
    protected $payment;

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
}
