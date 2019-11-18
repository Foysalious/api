<?php namespace Sheba\Transactions\Wallet;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sheba\FraudDetection\Exceptions\FraudDetectionServerError;
use Sheba\FraudDetection\Repository\TransactionRepository;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use Sheba\Transactions\Wallet\Jobs\FraudTransactionJob;
use Sheba\Transactions\Wallet\Jobs\WalletTransactionJob;

class WalletTransactionHandler extends WalletTransaction
{
    use ModificationFields;

    protected $amount;
    protected $log;
    protected $type;
    /** @var TransactionDetails $transaction_details */
    protected $transaction_details;
    private $source;

    /**
     * @param array $extras
     * @param bool $isJob
     * @return Model
     */
    public function store($extras = [], $isJob = false)
    {
        try {
            if (empty($this->type) || empty($this->amount) || empty($this->model)) throw new InvalidWalletTransaction();
            if (!$isJob) $extras = $this->withCreateModificationField((new RequestIdentification())->set($extras));

            $transaction = $this->storeTransaction($extras);
            try {
                $this->storeFraudDetectionTransaction(!$isJob);
            } catch (Exception $e) {
                WalletTransaction::throwException($e);
            }
            return $transaction;
        } catch (Exception $e) {
            WalletTransaction::throwException($e);
        }
        
        return null;
    }

    /**
     * @param array $data
     * @return Model
     */
    private function storeTransaction($data = [])
    {
        /** @var Model $transaction */
        $transaction = null;
        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$transaction) {
            $typeMethod = sprintf("%sWallet", $this->type);
            $this->$typeMethod();
            $data = array_merge($data, [
                'type' => ucfirst($this->type), 'log' => $this->log, 'created_at' => Carbon::now(), 'transaction_details' => $this->transaction_details ? $this->transaction_details->toString() : null, 'amount' => $this->amount
            ]);
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
     * @param bool $isJob
     * @throws FraudDetectionServerError
     */
    private function storeFraudDetectionTransaction($isJob = true)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $data = [
            'user_type' => strtolower(class_basename($this->model)),
            'user_id' => $this->model->id,
            'user_name' => $this->getName(),
            'source' => $this->source,
            'type' => $this->type,
            'log' => $this->log,
            'detail' => $this->transaction_details ? $this->transaction_details->toString() : null,
            'gateway' => $this->transaction_details ? $this->transaction_details->getGateway() : null,
            'gateway_trx_id' => $this->transaction_details ? $this->transaction_details->getTransactionID() : null,
            'amount' => $this->amount,
            'created_at' => Carbon::now()->format('Y-m-d H:s:i')
        ];

        if ($isJob) {
            dispatch((new FraudTransactionJob())->setData($data));
        } else {
            $fraudTransactionRepo = new TransactionRepository();
            $fraudTransactionRepo->store($data);
        }
    }

    /**
     * @return string|null
     */
    private function getName()
    {
        return !is_null($this->model->name) ? $this->model->name : (!is_null($this->model->profile) ? $this->model->profile->name : null);
    }

    public function storeFraudOnly()
    {
        try {

            if (empty($this->type) || empty($this->amount) || empty($this->model)) {
                throw new InvalidWalletTransaction();
            }
            $this->storeFraudDetectionTransaction(true);
        } catch (Exception $e) {
            WalletTransaction::throwException($e);
        }
    }

    /**
     * @param mixed $source
     * @return WalletTransactionHandler
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param array $data
     * @param bool $isJob
     * @return Model
     */
    public function recharge($data = [], $isJob = false)
    {

        try {
            if (empty($this->amount) || empty($this->model)) {
                throw new InvalidWalletTransaction();
            }
            $transaction = $this->setType('credit')->storeTransaction($data);
            $this->storeFraudDetectionTransaction(!$isJob);
            return $transaction;
        } catch (Exception $e) {
            WalletTransaction::throwException($e);
        }
        return null;
    }

    /**
     * @param mixed $type
     * @return WalletTransactionHandler
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $log
     * @return WalletTransactionHandler
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return WalletTransactionHandler
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $transaction_details
     * @return WalletTransactionHandler
     */
    public function setTransactionDetails(array $transaction_details)
    {
        $this->transaction_details = TransactionDetails::generateDetails($transaction_details);
        return $this;
    }

    /**
     * DISPATCH TRANSACTION STORE JOB
     * @param array $extras
     */
    public function dispatch($extras = [])
    {
        $extras = $this->withCreateModificationField((new RequestIdentification())->set($extras));
        dispatch((new WalletTransactionJob($this))->setExtras($extras));
    }
}