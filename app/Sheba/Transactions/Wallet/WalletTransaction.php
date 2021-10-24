<?php namespace Sheba\Transactions\Wallet;

use Exception;

class WalletTransaction
{
    /**  @var  HasWalletTransaction $model */
    protected $model;

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
     * @param \Throwable|Exception $e
     */
    public static function throwException($e)
    {
        logError($e);
    }

    /**
     * DECREASE WALLET BALANCE OF MODEL
     * @return void
     */
    protected function debitWallet()
    {
        $this->model->reload();
        $model = (get_class($this->model));
        $locked_model = ($model::whereId($this->model->id)->lockForUpdate()->first());
        $locked_model->wallet -= $this->amount;
        $locked_model->update();
        return $locked_model->wallet;
    }

    /**
     * INCREASE WALLET BALANCE OF MODEL
     * @return void
     */
    protected function creditWallet()
    {
        $this->model->reload();
        $model = (get_class($this->model));
        $locked_model = ($model::whereId($this->model->id)->lockForUpdate()->first());
        $locked_model->wallet += $this->amount;
        $locked_model->update();
        return $locked_model->wallet;
    }
}
