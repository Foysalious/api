<?php namespace App\Sheba\Repositories;

use App\Models\PartnerWalletSetting;
use Sheba\Repositories\BaseRepository;

class PartnerWalletSettingRepository extends BaseRepository
{
    public function __construct(PartnerWalletSetting $partnerWalletSetting)
    {
        parent::__construct();
        $this->setModel($partnerWalletSetting);
    }
}