<?php namespace App\Sheba\Business\Procurement;

use App\Models\Procurement;
use Illuminate\Database\QueryException;
use Sheba\Business\ProcurementStatusChangeLog\Creator;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Business\ProcurementPayment\Creator as PaymentCreator;

class Updater
{
    private $status;
    /** @var Procurement */
    private $procurement;
    private $statusLogCreator;
    private $procurementRepository;
    private $walletTransactionHandler;
    private $paymentCreator;


    public function __construct(ProcurementRepositoryInterface $procurement_repository, Creator $creator, WalletTransactionHandler $wallet_transaction_handler, PaymentCreator $payment_creator)
    {
        $this->procurementRepository = $procurement_repository;
        $this->statusLogCreator = $creator;
        $this->walletTransactionHandler = $wallet_transaction_handler;
        $this->paymentCreator = $payment_creator;
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function updateStatus()
    {
        try {
            $previous_status = $this->procurement->status;
            $this->procurementRepository->update($this->procurement, ['status' => $this->status]);
            $this->statusLogCreator->setProcurement($this->procurement)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
            $this->procurement->calculate();
            if ($this->status == 'served') {
                $this->walletTransactionHandler->setModel($this->procurement->getActiveBid()->bidder)->setAmount($this->procurement->due)->setSource(TransactionSources::SERVICE_PURCHASE)
                    ->setType('credit')->setLog("Credited for RFQ ID:" . $this->procurement->id)->dispatch();
                $this->paymentCreator->setProcurement($this->procurement)->setAmount($this->procurement->due)->setPaymentMethod('cod')->setPaymentType('Credit')->create();
                $this->notify();
            }
        } catch (QueryException $e) {
            throw  $e;
        }
        return $this->procurement;
    }

    private function notify()
    {
        $bid = $this->procurement->getActiveBid();
        $message = $bid->bidder->name . " has served your order";
        $link = config('sheba.business_url') . '/dashboard/procurement/orders/' . $this->procurement->id . '?bid=' . $bid->id;
        foreach ($this->procurement->owner->superAdmins as $member) {
            notify()->member($member)->send([
                'title' => $message,
                'type' => 'warning',
                'event_type' => get_class($bid),
                'event_id' => $bid->id,
                'link' => $link
            ]);
        }
    }
}