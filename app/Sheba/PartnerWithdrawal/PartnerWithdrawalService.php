<?php

namespace Sheba\PartnerWithdrawal;

use App\Models\Partner;
use App\Models\WithdrawalRequest;

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
        $newWithdrawal = WithdrawalRequest::create($data);
        $this->updateSetting($partner, $data);
        return $newWithdrawal;
    }

    public function updateSetting(Partner $partner, $data)
    {
        $this->updater->setSetting($partner->walletSetting)->setData($data)->update();
    }
}