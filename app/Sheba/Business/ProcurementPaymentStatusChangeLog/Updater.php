<?php namespace Sheba\Business\ProcurementPaymentStatusChangeLog;

use Sheba\Dal\ProcurementPaymentRequestStatusChangeLog\ProcurementPaymentRequestStatusChangeLogRepositoryInterface;

class Updater
{
    private $paymentStatusChangeLogRepo;

    public function __construct(ProcurementPaymentRequestStatusChangeLogRepositoryInterface $payment_status_change_log_repo)
    {
        $this->paymentStatusChangeLogRepo = $payment_status_change_log_repo;
    }
}