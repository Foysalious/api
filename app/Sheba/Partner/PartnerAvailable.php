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

    public function available($date, $preferred_time, $category_id)
    {
        if ($this->_partnerOnLeave($date)) {
            return 0;
        }
        if (!$this->_worksAtDayAndTime($date, $preferred_time)) {
            return 0;
        }
        $rent_car_ids = array_map('intval', explode(',', env('RENT_CAR_IDS')));
        if (!in_array($category_id, $rent_car_ids)) {
            if (!((scheduler($this->partner)->isAvailableForCategory($date, explode('-', $preferred_time)[0], Category::find((int)$category_id))))->get('is_available')) {
                return 0;
            }
        }
        return 1;
    }

    private function _partnerOnLeave($date)
    {
        $date = $date . ' ' . date('H:i:s');
        return $this->partner->runningLeave($date) != null ? true : false;
    }

    private function _worksAtDayAndTime($date, $time)
    {
        $day = Carbon::parse($date)->format('l');
        $working_day = $this->partner->workingHours->where('day', $day)->first();
        if (!$working_day) return false;
        $start_time = Carbon::parse(explode('-', $time)[0]);
        return $start_time->gte(Carbon::parse($working_day->start_time)) && $start_time->lte(Carbon::parse($working_day->end_time));
    }
}