<?php namespace Sheba;

interface HasWallet
{
    public function rechargeWallet($amount, $transaction_data);

    public function minusWallet($amount, $transaction_data);

    public function creditWallet($amount);

    public function debitWallet($amount);

    public function walletTransaction($transaction_data);
}
