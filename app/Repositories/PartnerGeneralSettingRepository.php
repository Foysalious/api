<?php namespace App\Repositories;

use Sheba\Dal\PartnerGeneralSetting\Contract as PartnerGeneralSetting;
use Sheba\ModificationFields;

class PartnerGeneralSettingRepository
{
    use ModificationFields;

    protected $partnerGeneralSetting;

    public function __construct(PartnerGeneralSetting $partnerGeneralSetting)
    {
        $this->partnerGeneralSetting = $partnerGeneralSetting;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function storeSMSNotificationStatus($data)
    {
        return $this->partnerGeneralSetting->create($this->withCreateModificationField($data));
    }

    /**
     * @param $partner
     * @return mixed
     */
    public function getSMSNotificationStatus($partner)
    {
        $status = $this->partnerGeneralSetting->find($partner);
        return $status;
    }
}