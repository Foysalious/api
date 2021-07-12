<?php namespace App\Sheba\Business\PayrollSetting;

use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepo;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepo;

class LastWorkingDayOfMonth
{
    /*** @var BusinessWeekendRepo  */
    private $businessWeekRepo;
    /*** @var BusinessHolidayRepo */
    private $businessHolidayRepo;

    public function __construct()
    {
        $this->businessWeekRepo = app(BusinessWeekendRepo::class);
        $this->businessHolidayRepo = app(BusinessHolidayRepo::class);
    }

    public function get($business, $last_day_of_month)
    {
        while ($last_day_of_month) {
            if (!$this->businessWeekRepo->isWeekendByBusiness($business, $last_day_of_month) &&
                !$this->businessHolidayRepo->isHolidayByBusiness($business, $last_day_of_month)) break;
            $last_day_of_month = $last_day_of_month->subDay(1);
        }
        return $last_day_of_month;
    }
}