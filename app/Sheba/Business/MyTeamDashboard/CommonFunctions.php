<?php namespace Sheba\Business\MyTeamDashboard;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Helpers\TimeFrame;

class CommonFunctions
{
    /** @var Business */
    private $business;
    /** @var Carbon */
    private $startDate;
    /** @var Carbon */
    private $endDate;
    /** @var BusinessHolidayRepoInterface $businessHoliday */
    private $businessHoliday;
    /** @var BusinessWeekendRepoInterface $businessWeekend */
    private $businessWeekend;

    /**
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     */
    public function __construct(BusinessHolidayRepoInterface $business_holiday_repo,
                                BusinessWeekendRepoInterface $business_weekend_repo)
    {
        $this->businessHoliday = $business_holiday_repo;
        $this->businessWeekend = $business_weekend_repo;
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
     * @param TimeFrame $selected_date
     * @return $this
     */
    public function setSelectedDate(TimeFrame $selected_date)
    {
        $this->startDate = $selected_date->start;
        $this->endDate = $selected_date->end;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWeekendHoliday()
    {
        $business_weekend = $this->businessWeekend->getAllByBusiness($this->business);
        $business_holiday = $this->businessHoliday->getAllByBusiness($this->business);

        $dates_of_holidays_formatted = [];
        $weekend_day = $business_weekend->pluck('weekday_name')->toArray();
        foreach ($business_holiday as $holiday) {
            $start_date = Carbon::parse($holiday->start_date);
            $end_date = Carbon::parse($holiday->end_date);
            for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                $dates_of_holidays_formatted[] = $d->format('Y-m-d');
            }
        }

        return $this->isWeekend($this->startDate, $weekend_day)
            || $this->isHoliday($this->startDate, $dates_of_holidays_formatted);
    }

    /**
     * @param Carbon $date
     * @param $weekend_day
     * @return bool
     */
    private function isWeekend(Carbon $date, $weekend_day)
    {
        return in_array(strtolower($date->format('l')), $weekend_day);
    }

    /**
     * @param Carbon $date
     * @param $holidays
     * @return bool
     */
    private function isHoliday(Carbon $date, $holidays)
    {
        return in_array($date->format('Y-m-d'), $holidays);
    }
}