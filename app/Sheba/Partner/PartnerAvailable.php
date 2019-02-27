<?php

namespace App\Sheba\Partner;

use App\Models\Category;
use App\Models\Partner;
use Carbon\Carbon;

class PartnerAvailable
{
    private $partner;

    public function __construct($partner)
    {
        $this->partner = ($partner) instanceof Partner ? $partner : Partner::find($partner);
    }

    public function available(array $dates, $preferred_time, Category $category)
    {
        foreach ($dates as $date) {
            if ($this->_partnerOnLeave($date, $preferred_time)) {
                return 0;
            }
            if (!$this->_worksAtDayAndTime($date, $preferred_time)) {
                return 0;
            }
        }
        $rent_car_ids = array_map('intval', explode(',', env('RENT_CAR_IDS')));
        if (!in_array($category->id, $rent_car_ids)) {
            if (!((scheduler($this->partner)->isAvailable($dates, explode('-', $preferred_time)[0], $category)))->get('is_available')) {
                return 0;
            }
        }

        return 1;
    }

    private function _partnerOnLeave($date, $preferred_time)
    {
        $date = $date . ' ' . explode('-', $preferred_time)[0];
        return $this->partner->runningLeave($date) != null ? true : false;
    }

    private function _worksAtDayAndTime($date, $time)
    {
        $day = Carbon::parse($date)->format('l');
        $working_day = $this->partner->workingHours->where('day', $day)->first();
        if (!$working_day) return false;
        $start_time = Carbon::parse(explode('-', $time)[0]);
        $working_hour_start_time = Carbon::parse($working_day->start_time);
        $working_hour_end_time = Carbon::parse($working_day->end_time);
        $is_available = ($working_hour_end_time->notEqualTo($start_time) && $start_time->between($working_hour_start_time, $working_hour_end_time, true));
        return $is_available ? 1 : 0;
    }
}