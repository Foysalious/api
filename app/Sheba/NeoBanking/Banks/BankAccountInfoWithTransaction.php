<?php namespace App\Sheba\NeoBanking\Banks;


use Sheba\NeoBanking\Banks\BankAccountDetailInfo;
use Sheba\NeoBanking\Banks\BankTransaction;

class BankAccountInfoWithTransaction
{

    /** @var BankAccountDetailInfo $accountInfo */
    protected $accountInfo;
    /** @var BankTransaction[] $transactions */
    protected $transactions;

    /**
     * @param BankAccountDetailInfo $accountInfo
     * @return BankAccountInfoWithTransaction
     */
    public function setAccountInfo($accountInfo)
    {
        $this->accountInfo = $accountInfo;
        return $this;
    }

    /**
     * @param BankTransaction[] $transactions
     * @return BankAccountInfoWithTransaction
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
        return $this;
    }

    public function toArray()
    {

        return [
            'account_info' => $this->accountInfo->toArray(),
            'transactions' => array_map(function ($item) {
                return $item->toArray();
            }, $this->transactions)
        ];
    }

}
