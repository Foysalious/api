<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\Wallet;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpCommission;
use Sheba\TopUp\TopUpTrait;
use Sheba\TopUp\TopUpTransaction;

class Vendor extends Model implements TopUpAgent
{
    use Wallet;
    use TopUpTrait;
    protected $guarded = ['id'];

    public function topUpTransaction(TopUpTransaction $transaction)
    {
        $this->debitWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Debit', 'log' => $transaction->getLog()]);
    }

    /**
     * @return TopUpCommission
     */
    public function getCommission()
    {
        return new \Sheba\TopUp\Commission\Vendor();
    }
}