<?php namespace App\Sheba\Transactions\Wallet;


use Carbon\Carbon;
use Sheba\Dal\RobiTopupWalletTransaction\Model as RobiTopupWalletTransaction;
use Sheba\ModificationFields;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\InvalidWalletTransaction;
use Sheba\Transactions\Wallet\TransactionDetails;
use Sheba\Transactions\Wallet\WalletTransaction;
use Exception;

class RobiTopUpWalletTransactionHandler extends WalletTransaction
{
    use ModificationFields;

    protected $amount;
    protected $log;
    protected $type;
    /** @var TransactionDetails $transaction_details */
    protected $transaction_details;
    private   $source;

    /**
     * @param mixed $amount
     * @return RobiTopUpWalletTransactionHandler
     */
    public function setAmount($amount)
    {
        $this->amount = round($amount, 2);
        return $this;
    }

    /**
     * @param $log
     * @return $this
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool|int|null
     */
    public function store()
    {
        try {
            $log = null;
            if (empty($this->type) || empty($this->amount) )
                throw new InvalidWalletTransaction();
            if($this->type == Types::debit())
            {
                /** @noinspection PhpUndefinedFieldInspection */
                $this->model->robi_topup_wallet -= $this->amount;

            }
            elseif ($this->type == Types::credit())
            {
                /** @noinspection PhpUndefinedFieldInspection */
                $this->model->robi_topup_wallet += $this->amount;
            }
            $this->model->update();
            $data = [
                'affiliate_id' => $this->model->id,
                'type' => ucfirst($this->type),
                'log' => $this->log,
                'created_at' => Carbon::now(),
                'balance' => $this->getCalculatedBalance(),
                'amount' => $this->amount,
            ];
            $transaction_data = (new RobiTopupWalletTransaction())->fill($data);
            return $this->model->robi_topup_wallet_transactions()->save($transaction_data);
            
        } catch (Exception $e) {
            WalletTransaction::throwException($e);
        }
        return null;
    }


    private function getCalculatedBalance()
    {
        $last_inserted_transaction = $this->model->robi_topup_wallet_transactions()->orderBy('id', 'desc')->first();
        $last_inserted_balance = $last_inserted_transaction ? $last_inserted_transaction->balance : 0.00;
        return strtolower($this->type) == 'credit' ? $last_inserted_balance + $this->amount : $last_inserted_balance - $this->amount;
    }

}