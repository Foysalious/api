<?php namespace Sheba\Transactions\Wallet;


use Exception;
use Illuminate\Database\Eloquent\Model;
use Sheba\FraudDetection\Exceptions\FraudDetectionServerError;

class WalletTransactionHandler extends WalletTransaction
{
    private $source;

    /**
     * @param array $extras
     * @return Model
     * @throws InvalidWalletTransaction
     */
    public function store($extras = [])
    {
        try {
            if (empty($this->type) || empty($this->amount) || empty($this->model)) {
                throw new InvalidWalletTransaction();
            }
            $transaction = $this->storeTransaction($extras);
            $this->storeFraudDetectionTransaction($this->source);
            return $transaction;
        } catch (FraudDetectionServerError $e) {
            $this->throwException($e);
        } catch (Exception $e) {
            $this->throwException($e);
        }
        return null;
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
     * @return Model
     */
    public function recharge($data = [])
    {

        try {
            if (empty($this->amount) || empty($this->model)) {
                throw new InvalidWalletTransaction();
            }
            $transaction = $this->setType('credit')->storeTransaction($data);
            $this->storeFraudDetectionTransaction($this->source);
            return $transaction;
        } catch (FraudDetectionServerError $e) {
            $this->throwException($e);
        } catch (Exception $e) {
            $this->throwException($e);
        }
        return null;
    }
}
