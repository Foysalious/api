<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use Sheba\FraudDetection\TransactionSources;
use Sheba\TopUp\Vendor\VendorFactory;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

trait TopUpTrait
{
    public function refund($amount, $log)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->creditWallet($amount);
        $this->walletTransaction(['amount' => $amount, 'type' => 'Credit', 'log' => $log]);*/
        /** @var HasWalletTransaction $model */
        $model=$this;
        (new WalletTransactionHandler())
            ->setModel($model)
            ->setAmount($amount)
            ->setSource(TransactionSources::TOP_UP)
            ->setType(Types::credit())
            ->setLog($log)
            ->dispatch();
    }

    public function deductFromAmbassador($amount, $log)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->debitWallet($amount);
        $this->walletTransaction(['amount' => $amount, 'type' => 'Debit', 'log' => $log]);*/
        /** @var HasWalletTransaction $model */
        $model=$this;
        (new WalletTransactionHandler())->setModel($model)->setSource(TransactionSources::TOP_UP)->setType(Types::debit())
            ->setAmount($amount)->setLog($log)->dispatch();
    }

    public function calculateCommission($amount, TopUpVendor $topup_vendor)
    {
        return (double)$amount * ($this->agentCommission($topup_vendor) / 100);
    }

    public function calculateAmbassadorCommission($amount, TopUpVendor $topup_vendor)
    {
        return (double)$amount * ($this->ambassadorCommission($topup_vendor) / 100);
    }

    public function agentCommission($topup_vendor)
    {
        return (double)$topup_vendor->commissions()->where('type', get_class($this))->first()->agent_commission;
    }

    public function ambassadorCommission($topup_vendor)
    {
        return (double)$topup_vendor->commissions()->where('type', get_class($this))->first()->ambassador_commission;
    }

    public function topUpOrders()
    {
        return $this->morphMany(TopUpOrder::class, 'agent');
    }
}
