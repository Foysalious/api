<?php namespace Sheba\Business\ProcurementPaymentRequest;

use App\Models\Partner;
use Illuminate\Database\QueryException;
use Sheba\Business\Procurement\OrderClosedHandler;
use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Sheba\Business\ProcurementPayment\Creator as PaymentCreator;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;
use Sheba\Business\ProcurementPaymentRequestStatusChangeLog\Creator;
use App\Models\Procurement;
use App\Models\Bid;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use DB;

class Updater
{
    private $procurementPaymentRequestRepository;
    private $procurementRepository;
    /** @var Procurement $procurement */
    private $procurement;
    private $bid;
    /** @var ProcurementPaymentRequest */
    private $paymentRequest;
    private $note;
    private $status;
    private $statusLogCreator;
    private $paymentCreator;
    private $data;
    private $walletTransactionHandler;
    /** @var OrderClosedHandler */
    private $orderClosedHandler;


    public function __construct(ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repository,
                                Creator $creator, PaymentCreator $payment_creator, ProcurementRepositoryInterface $procurement_repository,
                                WalletTransactionHandler $wallet_transaction_handler, OrderClosedHandler $order_closed_handler)
    {
        $this->procurementPaymentRequestRepository = $procurement_payment_request_repository;
        $this->procurementRepository = $procurement_repository;
        $this->statusLogCreator = $creator;
        $this->paymentCreator = $payment_creator;
        $this->data = [];
        $this->walletTransactionHandler = $wallet_transaction_handler;
        $this->orderClosedHandler = $order_closed_handler;
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

    public function setPaymentRequest(ProcurementPaymentRequest $payment_request)
    {
        $this->paymentRequest = $payment_request;
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
        $payment_request = null;
        try {
            DB::transaction(function () use (&$payment_request) {
                $payment_request = $this->procurementPaymentRequestRepository->where('id', $this->paymentRequest->id)->first();
                $this->setPaymentRequest($payment_request);
                $previous_status = $this->paymentRequest->status;
                if ($previous_status == $this->status) return null;
                $payment_request = $this->procurementPaymentRequestRepository->update($this->paymentRequest, $this->data);
                $this->statusLogCreator->setPaymentRequest($this->paymentRequest)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
                if ($this->status == config('b2b.PROCUREMENT_PAYMENT_STATUS')['approved']) {
                    $amount = $this->paymentRequest->amount;
                    $this->paymentCreator->setPaymentType('Debit')->setPaymentMethod('cheque')->setProcurement($this->procurement)->setAmount($amount)
                        ->setLog('Payment request #' . $this->paymentRequest->id . ' has been approved.')->create();
                    /** @var Partner $partner */
                    $partner = $this->procurement->getActiveBid()->bidder;
                    $partner->minusWallet($amount, ['log' => 'Received money for RFQ Order #' . $this->procurement->id]);
                    $this->procurementRepository->update($this->procurement, ['partner_collection' => $this->procurement->partner_collection + $amount]);
                    $this->orderClosedHandler->setProcurement($this->procurement)->run();

                }
            });
            $this->sendStatusChangeNotification();
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

    private function getLockedPaymentInstance()
    {

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

    private function sendStatusChangeNotification()
    {
        $message = $this->paymentRequest->procurement->owner->name . ' has ' . $this->status . ' your payment request #' . $this->paymentRequest->id;
        $link = config('sheba.partners_url') . '/' . $this->paymentRequest->bid->bidder->sub_domain . '/rfq-orders/' . $this->paymentRequest->procurement->id;
        notify()->partner($this->paymentRequest->bid->bidder)->send([
            'title' => $message,
            'type' => 'warning',
            'event_type' => get_class($this->paymentRequest),
            'event_id' => $this->paymentRequest->id,
            'link' => $link
        ]);
    }
}
