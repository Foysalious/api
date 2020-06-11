<?php namespace Sheba\Reward;

use Sheba\Transactions\Wallet\HasWalletTransaction;

interface Rewardable extends HasWalletTransaction
{
    public function rechargeWallet($amount, $transaction_data);
}