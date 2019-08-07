<?php namespace Sheba;


use App\Models\PartnerTransaction;

interface HasWallet
{
    public function rechargeWallet($amount, $transaction_data): PartnerTransaction;

    public function minusWallet($amount, $transaction_data): PartnerTransaction;

    public function creditWallet($amount);

    public function debitWallet($amount);

    public function walletTransaction($transaction_data);
}