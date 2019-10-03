<?php

namespace App\Sheba\Partner;

use App\Models\Category;
use App\Models\Partner;
use Carbon\Carbon;
use Sheba\Checkout\Partners\PartnerUnavailabilityReasons;

class PartnerAvailable
{
    /** @var Partner */
    private $partner;
    /** @var bool */
    private $isAvailable;
    /** @var string */
    private $unavailabilityReason;

    public function __construct($partner)
    {
        $this->partner = ($partner) instanceof Partner ? $partner : Partner::find($partner);
    }

    /**
     * @param array $dates
     * @param $preferred_time
     * @param Category $category
     */
    public function check(array $dates, $preferred_time, Category $category)
    {
        foreach ($dates as $date) {
            if ($this->_partnerOnLeave($date, $preferred_time)) {
                $this->isAvailable = false;
                $this->unavailabilityReason = PartnerUnavailabilityReasons::ON_LEAVE;
                return;
            }
            if (!$this->_worksAtDayAndTime($date, $preferred_time)) {
                $this->isAvailable = false;
                $this->unavailabilityReason = PartnerUnavailabilityReasons::WORKING_HOUR;
                return;
            }
        }

        $rent_car_ids = array_map('intval', explode(',', env('RENT_CAR_IDS')));
        if (!in_array($category->id, $rent_car_ids)) {
            if (!((scheduler($this->partner)->isAvailable($dates, explode('-', $preferred_time)[0], $category)))->get('is_available')) {
                $this->isAvailable = false;
                $this->unavailabilityReason = PartnerUnavailabilityReasons::RESOURCE_BOOKED;
                return;
            }
        }

        $this->isAvailable = true;
    }

    /**
     * @return int
     */
    public function getAvailability()
    {
        return $this->isAvailable ? 1 : 0;
    }

    /**
     * @return string
     */
    public function getUnavailabilityReason()
    {
        return $this->unavailabilityReason;
    }

    /**
     * @param array $dates
     * @param $preferred_time
     * @param Category $category
     * @return int
     */
    public function available(array $dates, $preferred_time, Category $category)
    {
        $this->check($dates, $preferred_time, $category);
        return $this->getAvailability();
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