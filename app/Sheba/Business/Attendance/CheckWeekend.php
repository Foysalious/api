<?php namespace Sheba\Business\Attendance;

use Carbon\Carbon;

class CheckWeekend
{
    /**
     * @param $start_date
     * @param $weekend_settings
     * @return array|mixed
     */
    public function getWeekendDays($start_date, $weekend_settings)
   {
       $weekend_setting_days = [];

       foreach ($weekend_settings as $weekend_setting) {
           $weekend_setting_start_date = Carbon::parse($weekend_setting->start_date);
           $weekend_setting_end_date = $weekend_setting->end_date ?: Carbon::now();

           if (!$start_date->between($weekend_setting_start_date, $weekend_setting_end_date)) continue;

           $weekend_setting_days = json_decode($weekend_setting->weekday_name);
           break;
       }

       return $weekend_setting_days;
   }
}