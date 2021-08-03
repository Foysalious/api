<?php namespace Sheba\Payment;

use App\Models\Payment;
use App\Repositories\PaymentStatusChangeLogRepository;

class StatusChanger
{
    /** @var PaymentStatusChangeLogRepository */
    private $logRepo;

    /** @var Payment */
    private $payment;

    public function __construct(PaymentStatusChangeLogRepository $repo)
    {
        $this->logRepo = $repo;
    }

    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        $this->logRepo->setPayment($this->payment);
        return $this;
    }

    /**
     * @param string $error_details JSON transaction details
     * @return Payment
     */
    public function changeToInitiationFailed($error_details)
    {
        $this->logRepo->create([
            'to'                  => Statuses::INITIATION_FAILED,
            'from'                => $this->payment->status,
            'transaction_details' => $error_details
        ]);
        $this->payment->status              = Statuses::INITIATION_FAILED;
        $this->payment->transaction_details = $error_details;
        $this->payment->request_payload     = json_encode(request()->all());
        $this->payment->update();
        return $this->payment;
    }

    /**
     * @param string $success_details JSON transaction details
     * @return Payment
     */
    public function changeToValidated($success_details)
    {
        $this->payment->reload();
        if ($this->payment->status == Statuses::VALIDATED) return $this->payment;
        $this->logRepo->create([
            'to'                  => Statuses::VALIDATED,
            'from'                => $this->payment->status,
            'transaction_details' => $this->payment->transaction_details
        ]);
        $this->payment->status              = Statuses::VALIDATED;
        $this->payment->transaction_details = $success_details;
        $this->payment->request_payload     = json_encode(request()->all());
        $this->payment->update();
        return $this->payment;
    }

    /**
     * @param string $error_details JSON transaction details
     * @return Payment
     */
    public function changeToValidationFailed(string $error_details): Payment
    {
        $this->logRepo->create([
            'to' => Statuses::VALIDATION_FAILED,
            'from' => $this->payment->status,
            'transaction_details' => $this->payment->transaction_details
        ]);

        $this->payment->status = Statuses::VALIDATION_FAILED;
        $this->payment->transaction_details = $error_details;
        $this->payment->request_payload = json_encode(request()->all());
        $this->payment->update();

        return $this->payment;
    }
}
