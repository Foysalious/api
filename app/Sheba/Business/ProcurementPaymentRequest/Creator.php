<?php namespace Sheba\Business\ProcurementPaymentRequest;

use App\Models\Bid;
use App\Models\Procurement;
use Illuminate\Database\QueryException;
use phpDocumentor\Reflection\DocBlock\Description;
use Sheba\Dal\ProcurementPaymentRequest\Model;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;
use Sheba\Notification\NotificationCreated;

class Creator
{
    private $procurementPaymentRequestRepository;
    private $procurement;
    private $bid;
    private $amount;
    private $shortDescription;
    private $paymentRequest;
    private $allPaymentRequest;
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

    public function setPaymentRequest($payment_request)
    {
        $this->paymentRequest = $this->procurementPaymentRequestRepository->find((int)$payment_request);
        return $this;
    }

    public function getAll()
    {
        $this->allPaymentRequest = $this->procurement->paymentRequests;
        $payment_request = [];
        foreach ($this->allPaymentRequest as $payment) {
            array_push($payment_request, [
                'id' => $payment->id,
                'procurement_id' => $payment->procurement_id,
                'bid_id' => $payment->bid_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'color' => constants('PROCUREMENT_PAYMENT_STATUS')[$payment->status],
                'note' => $payment->note,
                'created_at' => $payment->created_at->format('d/m/y')
            ]);
        }
        return $payment_request;
    }

    public function isCapableForPaymentRequest()
    {
        $price = $this->bid->price;
        $already_requested_amount = (double)$this->procurement->paymentRequests()->where('status', '<>', 'rejected')->sum('amount');
        $new_total_amount_after_request = $already_requested_amount + $this->amount;

        if ($new_total_amount_after_request > $price) {
            return false;
        }
        return true;
    }

    public function paymentRequestCreate()
    {
        $this->makePaymentRequestData();
        try {
            $payment_request = $this->procurementPaymentRequestRepository->create($this->data);
            $this->sendPaymentRequestCreateNotification($payment_request);
        } catch (QueryException $e) {
            throw  $e;
        }
        return $payment_request;
    }

    public function makePaymentRequestData()
    {
        $this->data = [
            'procurement_id' => $this->procurement->id,
            'bid_id' => $this->bid->id,
            'amount' => (double)$this->amount,
            'short_description' => $this->shortDescription
        ];
    }

    public function getPaymentRequestData()
    {
        return [
            'id' => $this->paymentRequest->id,
            'procurement_id' => $this->paymentRequest->procurement_id,
            'status' => $this->paymentRequest->status,
            'bid_id' => $this->paymentRequest->bid_id,
            'amount' => (double)$this->paymentRequest->amount,
            'short_description' => $this->paymentRequest->short_description,
            'note' => $this->paymentRequest->note,
            'created_at' => $this->paymentRequest->created_at->format('d/m/y'),
        ];
    }

    private function sendPaymentRequestCreateNotification(Model $payment_request)
    {
        $message = $this->bid->bidder->name . ' created payment request #' . $this->bid->procurement->id;
        $link = config('sheba.business_url') . '/dashboard/procurement/orders/' . $this->bid->procurement_id . '/bill?bid=' . $this->bid->id;
        foreach ($payment_request->procurement->owner->superAdmins as $member) {
            notify()->member($member)->send([
                'title' => $message,
                'type' => 'warning',
                'event_type' => get_class($this->bid),
                'event_id' => $this->bid->id,
                'link' => $link
            ]);
            event(new NotificationCreated([
                'notifiable_id' => $member->id,
                'notifiable_type' => "member",
                'event_id' => $this->bid->id,
                'event_type' => "bid",
                'title' => $message,
                'message' => $message,
                'link' => $link
            ], $this->bid->bidder->id, get_class($this->bid->bidder)));
        }
    }
}