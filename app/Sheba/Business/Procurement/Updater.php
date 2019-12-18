<?php namespace App\Sheba\Business\Procurement;

use App\Models\Procurement;
use Illuminate\Database\QueryException;
use Sheba\Business\ProcurementStatusChangeLog\Creator;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Updater
{

    private $status;
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
}