<?php namespace Sheba\Business\Procurement;


use App\Models\Partner;
use App\Models\Procurement;
use App\Sheba\Business\Procurement\Updater;
use Carbon\Carbon;
use Sheba\Dal\ProcurementPayment\ProcurementPaymentRepositoryInterface;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class OrderClosedHandler
{
    /** @var Procurement Procurement */
    private $procurement;
    /** @var WalletTransactionHandler */
    private $walletTransactionHandler;

    private $procurementUpdater;

    public function __construct(WalletTransactionHandler $wallet_transaction_handler, Updater $procurement_updater)
    {
        $this->walletTransactionHandler = $wallet_transaction_handler;
        $this->procurementUpdater = $procurement_updater;
    }


    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement->fresh();
        return $this;
    }

    public function run()
    {
        if (!$this->procurement->isServed() || $this->procurement->isClosed()) return;
        $this->procurement->calculate();
        if ($this->procurement->due > 0) return;
        /** @var Partner $partner */
        $partner = $this->procurement->getActiveBid()->bidder;
        $price = $this->procurement->totalPrice;
        $price_after_commission = $price - (($price * $partner->commission) / 100);
        $this->procurementUpdater->setProcurement($this->procurement)->setClosedAndPaidAt(Carbon::now())->update();
        if ($price_after_commission > 0) {
            $this->walletTransactionHandler->setModel($partner)->setAmount($price_after_commission)
                ->setSource(TransactionSources::SERVICE_PURCHASE)
                ->setType('credit')->setLog("Credited for RFQ ID:" . $this->procurement->id)->dispatch();
        }
    }

}