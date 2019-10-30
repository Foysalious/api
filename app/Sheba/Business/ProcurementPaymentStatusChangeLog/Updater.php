<?php namespace Sheba\Business\ProcurementPaymentStatusChangeLog;

use Sheba\Repositories\Business\ProcurementPaymentStatusChangeLogRepository;

class Updater
{
    private $paymentStatusChangeLogRepo;

    public function __construct(ProcurementPaymentStatusChangeLogRepository $payment_status_change_log_repo)
    {
        $this->paymentStatusChangeLogRepo = $payment_status_change_log_repo;
    }
}