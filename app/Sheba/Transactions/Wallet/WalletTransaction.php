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
     * @param Exception $e
     */
    public static function throwException($e)
    {
        app('sentry')->captureException($e);
    }

    /**
     * DECREASE WALLET BALANCE OF MODEL
     * @return void
     */
    protected function debitWallet()
    {
        $this->model->reload();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->model->wallet -= $this->amount;
        $this->model->update();
    }

    /**
     * INCREASE WALLET BALANCE OF MODEL
     * @return void
     */
    protected function creditWallet()
    {
        $this->model->reload();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->model->wallet += $this->amount;
        $this->model->update();
    }
}
