<?php namespace Sheba\Business\ProcurementPaymentStatusChangeLog;

use Sheba\Repositories\Business\ProcurementPaymentRequestStatusChangeLogRepository;

class Updater
{
    private $paymentStatusChangeLogRepo;

    public function __construct(ProcurementPaymentRequestStatusChangeLogRepository $payment_status_change_log_repo)
    {
        $this->paymentStatusChangeLogRepo = $payment_status_change_log_repo;
    }
}