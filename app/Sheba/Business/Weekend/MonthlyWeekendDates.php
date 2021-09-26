<?php namespace App\Sheba\Business\Weekend;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\BusinessWeekendSettings\BusinessWeekendSettingsRepo;

class MonthlyWeekendDates
{
    private $business;
    private $timeFrame;
    private $businessWeekendSettingsRepo;

    /**
     * @param BusinessWeekendSettingsRepo $business_weekend_settings_repo
     */
    public function __construct(BusinessWeekendSettingsRepo $business_weekend_settings_repo)
    {
        $this->businessWeekendSettingsRepo = $business_weekend_settings_repo;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param $time_frame
     * @return $this
     */
    public function setTimeFrame($time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    /**
     * @return array
     */
    public function getWeekends()
    {
        $weekend_settings = $this->businessWeekendSettingsRepo->getAllByBusiness($this->business);
        $start_date = $this->timeFrame->start;
        $end_date = $this->timeFrame->end;
        $weekend_dates = [];

        for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
            foreach ($weekend_settings as $weekend_setting) {
                $weekend_setting_start_date = Carbon::parse($weekend_setting->start_date);
                $weekend_setting_end_date = $weekend_setting->end_date ?: $end_date;

                if (!$start_date->between($weekend_setting_start_date, $weekend_setting_end_date)) continue;

                $weekend_setting_days = json_decode($weekend_setting->weekday_name);
                $today_name = strtolower($start_date->format('l'));

                if (in_array($today_name, $weekend_setting_days)) {
                    array_push($weekend_dates, $start_date->format('Y-m-d'));
                    break;
                }
            }
        }
        return $weekend_dates;
    }
}