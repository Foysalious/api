<?php namespace Sheba\Business\ProcurementPaymentRequest;

use Illuminate\Database\QueryException;
use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Sheba\Business\ProcurementPayment\Creator as PaymentCreator;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;
use Sheba\Business\ProcurementPaymentRequestStatusChangeLog\Creator;
use App\Models\Procurement;
use App\Models\Bid;

class Updater
{
    private $procurementPaymentRequestRepository;
    private $procurement;
    private $bid;
    private $paymentRequest;
    private $note;
    private $status;
    private $statusLogCreator;
    private $paymentCreator;
    private $data;


    public function __construct(ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repository, Creator $creator, PaymentCreator $payment_creator)
    {
        $this->procurementPaymentRequestRepository = $procurement_payment_request_repository;
        $this->statusLogCreator = $creator;
        $this->paymentCreator = $payment_creator;
        $this->data = [];
    }

    public function setProcurement($procurement)
    {
        $this->procurement = Procurement::findOrFail((int)$procurement);
        return $this;
    }

    public function getProcurement()
    {
        return $this->procurement;
    }

    public function setBid($bid)
    {
        $this->bid = Bid::findOrFail((int)$bid);
        return $this;
    }

    public function getBid()
    {
        return $this->bid;

    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function setPaymentRequest($payment_request)
    {
        $this->paymentRequest = $this->procurementPaymentRequestRepository->find((int)$payment_request);
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function paymentRequestUpdate()
    {
        $this->makePaymentRequestData();
        try {
            $previous_status = $this->paymentRequest->status;
            $payment_request = $this->procurementPaymentRequestRepository->update($this->paymentRequest, $this->data);
            $this->statusLogCreator->setPaymentRequest($this->paymentRequest)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
            if ($this->status == config('b2b.PROCUREMENT_PAYMENT_STATUS')['approved']) {
                $this->paymentCreator->setPaymentType('Debit')->setPaymentMethod('cheque')->setProcurement($this->procurement)
                    ->setAmount($this->paymentRequest->amount)->create();
                $bid = $this->procurement->getActiveBid();
                $bid->bidder->minusWallet($bid->price, ['log' => 'Received money for RFQ Order #' . $this->procurement->id]);
            }
        } catch (QueryException $e) {
            throw  $e;
        }
        return $payment_request;
    }

    public function makePaymentRequestData()
    {
        $this->data = [
            'note' => $this->note,
            'status' => $this->status
        ];
    }

    public function updateStatus()
    {
        try {
            $previous_status = $this->paymentRequest->status;
            $payment_request = $this->procurementPaymentRequestRepository->update($this->paymentRequest, ['status' => $this->status]);
            $this->statusLogCreator->setPaymentRequest($this->paymentRequest)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
        } catch (QueryException $e) {
            throw  $e;
        }
        return $payment_request;
    }
}