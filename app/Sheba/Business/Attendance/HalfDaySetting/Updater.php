<?php namespace Sheba\Business\Attendance\HalfDaySetting;

use App\Models\Business;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    private $business;
    private $halfDayConfig;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setHalfDayConfig($half_day_config)
    {
        $this->halfDayConfig = $half_day_config;
        return $this;
    }

    public function update()
    {
        $data = [
            'is_half_day_enable' => 1,
            'half_day_configuration' => $this->halfDayConfig
        ];
        return $this->business->update($this->withUpdateModificationField($data));
    }
}