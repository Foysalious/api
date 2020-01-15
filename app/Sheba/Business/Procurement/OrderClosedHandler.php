<?php namespace Sheba\Business\Procurement;


use App\Models\Partner;
use App\Models\Procurement;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class OrderClosedHandler
{
    /** @var Procurement Procurement */
    private $procurement;
    /** @var WalletTransactionHandler */
    private $walletTransactionHandler;

    public function __construct(WalletTransactionHandler $wallet_transaction_handler)
    {
        $this->walletTransactionHandler = $wallet_transaction_handler;
    }


    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function run()
    {
        $this->procurement->calculate();
        /** @var Partner $partner */
        $partner = $this->procurement->getActiveBid()->bidder;
        $price = $this->procurement->totalPrice;
        $price_after_commission = $price - (($price * $partner->commission) / 100);
        if ($price_after_commission > 0 && $this->procurement->due == 0) {
            $this->walletTransactionHandler->setModel($partner)->setAmount($price_after_commission)
                ->setSource(TransactionSources::SERVICE_PURCHASE)
                ->setType('credit')->setLog("Credited for RFQ ID:" . $this->procurement->id)->dispatch();
        }
    }

}