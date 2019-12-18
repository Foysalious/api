<?php namespace App\Sheba\Business\Procurement;

use App\Models\Procurement;
use Illuminate\Database\QueryException;
use Sheba\Business\ProcurementStatusChangeLog\Creator;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Notification\NotificationCreated;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Updater
{
    private $status;
    /** @var Procurement */
    private $procurement;
    private $statusLogCreator;
    private $procurementRepository;
    private $walletTransactionHandler;


    public function __construct(ProcurementRepositoryInterface $procurement_repository, Creator $creator, WalletTransactionHandler $wallet_transaction_handler)
    {
        $this->procurementRepository = $procurement_repository;
        $this->statusLogCreator = $creator;
        $this->walletTransactionHandler = $wallet_transaction_handler;
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
            $procurement = $this->procurementRepository->update($this->procurement, ['status' => $this->status]);
            $this->statusLogCreator->setProcurement($this->procurement)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
            $creator = app(\Sheba\Business\ProcurementPayment\Creator::class);
            $procurement->calculate();
            if ($this->status == 'served') {
                $this->walletTransactionHandler->setModel($procurement->getActiveBid()->bidder)->setAmount($procurement->due)->setSource(TransactionSources::SERVICE_PURCHASE)
                    ->setType('credit')->setLog("Credited for RFQ ID:" . $procurement->id)->dispatch();
                $creator->setProcurement($procurement)->setAmount($procurement->due)->setPaymentMethod('cod')->setPaymentType('Credit');
            }
        } catch (QueryException $e) {
            throw  $e;
        }
        return $procurement;
    }

    private function notify($message)
    {
        $bid = $this->procurement->getActiveBid();
        $link = config('sheba.business_url') . '/dashboard/procurement/orders/' . $this->procurement->id . '?bid=' . $bid->id;
        foreach ($this->procurement->owner->superAdmins as $member) {
            notify()->member($member)->send([
                'title' => $message,
                'type' => 'warning',
                'event_type' => get_class($bid),
                'event_id' => $bid->id,
                'link' => $link
            ]);
            event(new NotificationCreated([
                'notifiable_id' => $member->id,
                'notifiable_type' => "member",
                'event_id' => $bid->id,
                'event_type' => "bid",
                "title" => $message,
                'message' => $message,
                'link' => $link
            ], $bid->bidder_id, get_class($bid->bidder)));
        }
    }
}