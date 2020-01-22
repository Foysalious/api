<?php namespace Sheba\Business\Procurement;


use App\Models\Partner;
use App\Models\Procurement;
use Carbon\Carbon;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class OrderClosedHandler
{
    /** @var Procurement Procurement */
    private $procurement;
    /** @var WalletTransactionHandler */
    private $walletTransactionHandler;
    /** @var ProcurementRepositoryInterface */
    private $procurementRepository;

    public function __construct(WalletTransactionHandler $wallet_transaction_handler, ProcurementRepositoryInterface $procurement_repository)
    {
        $this->walletTransactionHandler = $wallet_transaction_handler;
        $this->procurementRepository = $procurement_repository;
    }


    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement->fresh();
        return $this;
    }

    public function run()
    {
        if (!$this->procurement->isServed() || $this->procurement->isClosedAndPaid()) return;
        $this->procurement->calculate();
        if ($this->procurement->due != 0) return;
        /** @var Partner $partner */
        $partner = $this->procurement->getActiveBid()->bidder;
        $price = $this->procurement->totalPrice;
        $price_after_commission = $price - (($price * $partner->commission) / 100);
        $this->procurementRepository->update($this->procurement, ['closed_and_paid_at' => Carbon::now()]);
        if ($price_after_commission > 0) {
            $this->walletTransactionHandler->setModel($partner)->setAmount($price_after_commission)
                ->setSource(TransactionSources::SERVICE_PURCHASE)
                ->setType('credit')->setLog("Credited for RFQ ID:" . $this->procurement->id)->dispatch();
        }
    }

}