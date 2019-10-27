<?php namespace Sheba\Transactions\Wallet;


use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sheba\FraudDetection\Exceptions\FraudDetectionServerError;
use Sheba\FraudDetection\Repository\TransactionRepository;
use Sheba\ModificationFields;

class WalletTransaction
{
    use ModificationFields;
    /**  @var  HasWalletTransaction $model */
    protected $model;
    protected $amount;
    protected $log;
    protected $type;
    /** @var TransactionDetails $transaction_details */
    protected $transaction_details;

    /**
     * @param HasWalletTransaction $model
     * @return $this
     */
    public function setModel(HasWalletTransaction $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param mixed $log
     * @return WalletTransaction
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @param mixed $type
     * @return WalletTransaction
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return WalletTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $transaction_details
     * @return WalletTransaction
     */
    public function setTransactionDetails(array $transaction_details)
    {
        $this->transaction_details = TransactionDetails::generateDetails($transaction_details);
        return $this;
    }

    /**
     * @param array $data
     * @return Model
     */
    protected function storeTransaction($data = [])
    {
        /** @var Model $transaction */
        $transaction = null;
        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$transaction) {
            $typeMethod = sprintf("%sWallet", $this->type);
            $this->$typeMethod();
            $data = $this->withCreateModificationField(array_merge($data, [
                'type' => ucfirst($this->type),
                'log' => $this->log,
                'created_at' => Carbon::now(),
                'transaction_details' => $this->transaction_details ? $this->transaction_details->toString() : null,
                'amount' => $this->amount
            ]));
            $transaction_data = $this->getTransactionClass()->fill($data);
            $transaction = $this->model->transactions()->save($transaction_data);
        });
        return $transaction;
    }

    /**
     * @return Model
     */
    private function getTransactionClass()
    {
        $name = class_basename($this->model);
        return app('App\\Models\\' . $name . 'Transaction');
    }

    /**
     * @param \Exception $e
     */
    protected function throwException($e)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Bugsnag::notifyException($e);
    }

    /**
     * @param $source
     * @throws FraudDetectionServerError
     */
    protected function storeFraudDetectionTransaction($source)
    {
        $fraudTransactionRepo = new TransactionRepository();
        /** @noinspection PhpUndefinedFieldInspection */
        $data = [
            'user_type' => strtolower(class_basename($this->model)),
            'user_id' => $this->model->id,
            'user_name' => $this->getName(),
            'source' => $source,
            'type' => $this->type,
            'log' => $this->log,
            'detail' => $this->transaction_details ? $this->transaction_details->toString() : null,
            'gateway' => $this->transaction_details ? $this->transaction_details->getGateway() : null,
            'gateway_trx_id' => $this->transaction_details ? $this->transaction_details->getTransactionID() : null,
            'amount' => $this->amount,
            'created_at' => Carbon::now()->format('Y-m-d H:s:i')
        ];
        $fraudTransactionRepo->store($data);
    }

    private function getName()
    {
        return !is_null($this->model->name) ? $this->model->name : (!is_null($this->model->profile) ? $this->model->profile->name : null);
    }

    private function debitWallet()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->model->wallet -= $this->amount;
        $this->model->update();
    }

    private function creditWallet()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->model->wallet += $this->amount;
        $this->model->update();
    }
}
