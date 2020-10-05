<?php


namespace Sheba\NeoBanking\Banks;


use Illuminate\Contracts\Support\Arrayable;
use Sheba\NeoBanking\Traits\JsonBreakerTrait;

class BankAccountDetailInfo implements Arrayable
{
    use JsonBreakerTrait;

    protected $account_name;
    protected $account_no;
    protected $balance;
    protected $minimum_transaction_amount;
    protected $transaction_error_msg;

    /**
     * @param mixed $account_name
     * @return BankAccountDetailInfo
     */
    public function setAccountName($account_name)
    {
        $this->account_name = $account_name;
        return $this;
    }

    /**
     * @param mixed $account_no
     * @return BankAccountDetailInfo
     */
    public function setAccountNo($account_no)
    {
        $this->account_no = $account_no;
        return $this;
    }

    /**
     * @param mixed $balance
     * @return BankAccountDetailInfo
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @param mixed $minimum_transaction_amount
     * @return BankAccountDetailInfo
     */
    public function setMinimumTransactionAmount($minimum_transaction_amount)
    {
        $this->minimum_transaction_amount = $minimum_transaction_amount;
        return $this;
    }

    /**
     * @param mixed $transaction_error_msg
     * @return BankAccountDetailInfo
     */
    public function setTransactionErrorMsg($transaction_error_msg)
    {
        $this->transaction_error_msg = $transaction_error_msg;
        return $this;
    }

}
