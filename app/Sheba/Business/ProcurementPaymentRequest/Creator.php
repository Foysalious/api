<?php namespace Sheba\Business\ProcurementPaymentRequest;

use App\Models\Bid;
use App\Models\Procurement;
use Illuminate\Database\QueryException;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;

class Creator
{
    private $procurementPaymentRequestRepository;
    private $procurement;
    private $bid;
    private $amount;
    private $shortDescription;
    private $data;


    public function __construct(ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repository)
    {
        $this->procurementPaymentRequestRepository = $procurement_payment_request_repository;
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

    public function setAmount($amount)
    {
        $this->amount = (double)$amount;
        return $this;
    }

    public function setShortDescription($short_description)
    {
        $this->shortDescription = $short_description;
        return $this;
    }

    public function paymentRequestCreate()
    {
        $this->makePaymentRequestData();
        try {
            $payment_request = $this->procurementPaymentRequestRepository->create($this->data);
        } catch (QueryException $e) {
            throw  $e;
        }
        return $payment_request;
    }

    public function makePaymentRequestData()
    {
        $this->data = [
            'procurement_id'=> $this->bid->id,
            'bid_id'=> $this->procurement->id,
            'amount' => (double)$this->amount,
            'short_description' => $this->shortDescription
        ];
    }
}