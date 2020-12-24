<?php namespace Sheba\Business\Attendance\HalfDaySetting;

use App\Models\Business;
use Carbon\Carbon;
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
        $requestConfig = json_decode($half_day_config, true);

        $requestConfig = [
            'first_half' => [
                'start_time' => Carbon::parse($requestConfig['first_half']['start_time'])->format('H:i').':59',
                'end_time' => Carbon::parse($requestConfig['first_half']['end_time'])->format('H:i').':59',
            ],
            'second_half' => [
                'start_time' => Carbon::parse($requestConfig['second_half']['start_time'])->format('H:i').':59',
                'end_time' => Carbon::parse($requestConfig['second_half']['end_time'])->format('H:i').':59',
            ]
        ];

        $requestConfig = json_encode($requestConfig);

        $this->halfDayConfig = $requestConfig;

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