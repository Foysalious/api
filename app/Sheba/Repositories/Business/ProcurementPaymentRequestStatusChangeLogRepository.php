<?php namespace Sheba\Repositories\Business;

use Sheba\Dal\ProcurementPaymentRequestStatusChangeLog\Model as ProcurementPaymentRequestStatusChangeLog;
use Sheba\Dal\ProcurementPaymentRequestStatusChangeLog\ProcurementPaymentRequestStatusChangeLogRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class ProcurementPaymentRequestStatusChangeLogRepository extends BaseRepository implements ProcurementPaymentRequestStatusChangeLogRepositoryInterface
{
    public function __construct(ProcurementPaymentRequestStatusChangeLog $procurement_payment_request_status_change_log)
    {
        parent::__construct();
        $this->setModel($procurement_payment_request_status_change_log);
    }
}