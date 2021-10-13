<?php namespace App\Repositories;

use Sheba\Dal\PartnerGeneralSetting\Contract as PartnerGeneralSettingRepo;
use Sheba\ModificationFields;

class PartnerGeneralSettingRepository
{
    use ModificationFields;

    protected $partnerGeneralSettingRepo;

    public function __construct(PartnerGeneralSettingRepo $partnerGeneralSettingRepo)
    {
        $this->partnerGeneralSettingRepo = $partnerGeneralSettingRepo;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function storeSMSNotificationStatus($data)
    {
        $userExist = $this->partnerGeneralSettingRepo->where('partner_id', $data['partner_id'])->first();
        if ($userExist) {
            return $userExist->update($this->withUpdateModificationField($data));
        }
        return $this->partnerGeneralSettingRepo->create($this->withCreateModificationField($data));
    }

    /**
     * @param $partnerId
     * @return mixed
     */
    public function getSMSNotificationStatus($partnerId): bool
    {
        $setting = $this->partnerGeneralSettingRepo->where('partner_id', $partnerId)->first();
        if ($setting) {
            return (bool) $setting->payment_completion_sms;
        }
        return false;
    }
}