<?php

namespace Sheba\PartnerWithdrawal;

use App\Models\Partner;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;

class PartnerWithdrawalService
{
    /** @var WalletSettingUpdater */
    private $updater;

    public function __construct(WalletSettingUpdater $updater)
    {
        $this->updater = $updater;
    }

    public function store(Partner $partner, array $data)
    {
        $creditLimitAmount = $partner->walletSetting->min_wallet_threshold;
        if ($creditLimitAmount < 0) {
            $creditLimitAmount = $data['amount']; // assuming min_wallet_threshold is less than 0 so we converted it to zero.
        } else {
            $creditLimitAmount = $creditLimitAmount + $data['amount'];
        }
        $creditLimitData = [
            'min_wallet_threshold' => $creditLimitAmount,
//            'reset_credit_limit_after' => Carbon::now()->addDays(7), //assuming one week for completing withdrawal request
            'log' => 'automatically updated credit limit because of withdrawal request'
        ];
        $newWithdrawal = WithdrawalRequest::create($data);
        $this->updateSetting($partner, $creditLimitData);
        return $newWithdrawal;
    }

    public function updateSetting(Partner $partner, $data)
    {
        $this->updater->setSetting($partner->walletSetting)->setData($data)->update();
    }
}