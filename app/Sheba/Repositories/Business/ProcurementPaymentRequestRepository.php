<?php namespace Sheba\Repositories\Business;

use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class ProcurementPaymentRequestRepository extends BaseRepository implements ProcurementPaymentRequestRepositoryInterface
{
    public function __construct(ProcurementPaymentRequest $procurement_payment_request)
    {
        parent::__construct();
        $this->setModel($procurement_payment_request);
    }
}