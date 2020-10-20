<?php namespace Sheba\Business\ProcurementPaymentRequestStatusChangeLog;

use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Sheba\Dal\ProcurementPaymentRequestStatusChangeLog\ProcurementPaymentRequestStatusChangeLogRepositoryInterface;

class Creator
{
    private $paymentStatusChangeLogRepo;
    private $data;
    private $previousStatus;
    private $paymentRequest;
    private $status;

    public function __construct(ProcurementPaymentRequestStatusChangeLogRepositoryInterface $payment_status_change_log_repo)
    {
        $this->paymentStatusChangeLogRepo = $payment_status_change_log_repo;
        $this->data = [];
    }

    public function setPaymentRequest(ProcurementPaymentRequest $payment_request)
    {
        $this->paymentRequest = $payment_request;
        return $this;
    }

    public function setPreviousStatus($previous_status)
    {
        $this->previousStatus = $previous_status;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }


    public function create()
    {
        $this->makeData();
        $this->paymentStatusChangeLogRepo->create($this->data);
    }

    private function makeData()
    {
        $this->data['payment_request_id'] = $this->paymentRequest->id;
        $this->data['from_status'] = $this->previousStatus;
        $this->data['to_status'] = $this->status;
    }
}