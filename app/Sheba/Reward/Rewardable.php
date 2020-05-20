<?php namespace Sheba\Reward;

interface Rewardable
{
    public function rechargeWallet($amount, $transaction_data);
}