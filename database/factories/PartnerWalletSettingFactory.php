<?php

namespace Database\Factories;

use App\Models\PartnerWalletSetting;
use Carbon\Carbon;

class PartnerWalletSettingFactory extends Factory
{
    protected $model = PartnerWalletSetting::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'min_withdraw_amount' => 0,
            'max_withdraw_amount' => 0,
            'security_money' => '100',
            'security_money_received' => 1,
            'min_wallet_threshold' => '100',
            'pending_withdrawal_amount' => 0,
        ]);
    }
}