<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\BaseModel;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Wallet\Wallet;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpCommission;
use Sheba\TopUp\TopUpTrait;
use Sheba\TopUp\TopUpTransaction;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Vendor extends BaseModel implements TopUpAgent, HasWalletTransaction
{
    use Wallet;
    use TopUpTrait;
    protected $guarded = ['id'];

    public function topUpTransaction(TopUpTransaction $transaction)
    {
       /*
        * WALLET TRANSACTION NEED TO REMOVE
        *  $this->debitWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Debit', 'initiator_type' => "App\\Models\\TopUpOrder",
            'initiator_id' => $transaction->getTopUpOrder()->id, 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())
            ->setModel($this)
            ->setAmount($transaction->getAmount())
            ->setSource(TransactionSources::TOP_UP)
            ->setType('debit')
            ->setLog($transaction->getLog())
            ->dispatch(['initiator_type' => "App\\Models\\TopUpOrder", 'initiator_id' => $transaction->getTopUpOrder()->id]);
    }

    public function transactions()
    {
        return $this->hasMany(VendorTransaction::class);
    }

    /**
     * @return TopUpCommission
     */
    public function getCommission()
    {
        return new \Sheba\TopUp\Commission\Vendor();
    }

    public function topups()
    {
        return $this->hasMany(TopUpOrder::class, 'agent_id')->where('agent_type', 'App\\Models\\Vendor');
    }

    public function getMobile()
    {
        return '+8801678242934';
    }
}
