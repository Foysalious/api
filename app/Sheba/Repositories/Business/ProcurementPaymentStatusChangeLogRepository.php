<?php namespace Sheba\Repositories\Business;

use Sheba\Dal\ProcurementPaymentStatusChangeLog\Model as ProcurementPaymentStatusChangeLog;
use Sheba\Repositories\Interfaces\ProcurementPaymentStatusChangeLogRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class ProcurementPaymentStatusChangeLogRepository extends BaseRepository implements ProcurementPaymentStatusChangeLogRepositoryInterface
{
    public function __construct(ProcurementPaymentStatusChangeLog $procurement_payment_status_change_log)
    {
        parent::__construct();
        $this->setModel($procurement_payment_status_change_log);
    }
}