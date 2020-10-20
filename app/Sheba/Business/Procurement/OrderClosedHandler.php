<?php namespace Sheba\Business\Procurement;


use App\Models\Bid;
use App\Models\Partner;
use App\Models\Procurement;
use Carbon\Carbon;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Transactions\Types;
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
        /** @var Bid $bid */
        $bid = $this->procurement->getActiveBid();
        $price = $this->procurement->totalPrice;
        $price_after_commission = $price - (($price * $this->getCommission($bid)) / 100);
        $this->procurementRepository->update($this->procurement, ['closed_and_paid_at' => Carbon::now()]);
        if ($price_after_commission > 0) {
            $this->walletTransactionHandler->setModel($bid->bidder)->setAmount($price_after_commission)
                ->setSource(TransactionSources::SERVICE_PURCHASE)
                ->setType(Types::credit())->setLog("Credited for RFQ ID:" . $this->procurement->id)->dispatch();
        }
    }

    private function getCommission(Bid $bid)
    {
        if ($bid->commission_percentage) return $bid->commission_percentage;
        return $bid->bidder->commission;
    }

}
