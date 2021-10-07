<?php namespace App\Repositories;

use Sheba\Dal\PartnerGeneralSetting\Contract as PartnerGeneralSetting;
use Sheba\ModificationFields;

class PartnerGeneralSettingRepository
{
    use ModificationFields;

    public function storeSMSNotificationStatus($data)
    {
        return PartnerGeneralSetting::create($this->modificationFields($data));
    }

    public function getSMSNotificationStatus($partner)
    {
        $status = PartnerGeneralSetting::findOrFail($partner);
        return $status;
    }
}