<?php namespace Sheba\Business\ProcurementOrder;

use App\Models\Bid;
use App\Models\Procurement;
use App\Sheba\Repositories\Business\BidRepository;
use Illuminate\Database\QueryException;
use DB;
use Sheba\Business\Bid\Bidder;
use Sheba\Repositories\Business\RfqOrderRepository;
use Sheba\Repositories\Interfaces\BidItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\BidItemRepositoryInterface;

class Creator
{
    private $rfqOrderRepository;
    private $procurement;
    private $bid;
    private $amount;
    private $note;
    private $shortDescription;
    private $data;


    public function __construct(RfqOrderRepository $rfq_order_repository)
    {
        $this->rfqOrderRepository = $rfq_order_repository;
        $this->data = [];
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setBid(Bid $bid)
    {
        $this->bid = $bid;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = (double)$amount;
        return $this;
    }

    public function setNote($note)
    {
        $this->note = (double)$note;
        return $this;
    }

    public function setShortDescription($short_description)
    {
        $this->shortDescription = (double)$short_description;
        return $this;
    }

    public function paymentRequestCreate()
    {
        $this->makePaymentRequestData();
        try {
            $payment_request = $this->rfqOrderRepository->create($this->data);
        } catch (QueryException $e) {
            throw  $e;
        }
        return $payment_request;
    }

    public function makePaymentRequestData()
    {
        $this->data = [
            'amount' => $this->amount,
            'note' => $this->note,
            'short_description' => $this->shortDescription
            ];
    }
}