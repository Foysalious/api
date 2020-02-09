<?php namespace Sheba\Business\Attendance\Monthly;


use App\Models\Business;
use App\Models\BusinessMember;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\BusinessHoliday\EloquentImplementation;
use Sheba\Helpers\TimeFrame;
use Sheba\Dal\BusinessWeekend\EloquentImplementation as BusinessWeeklyRepositoryInterface;
use Sheba\Dal\Attendance\EloquentImplementation as AttendRepositoryInterface;
class Stat
{
    private $present;
    private $onTime;
    private $late;
    private $leftEarly;
    private $absent;
    private $workingDay;
    private $isCalculated;
    /** @var BusinessMember */
    private $businessMember;
    /** @var Business */
    private $business;
    /** @var Attendance[] */
    private $attendances;
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    private $businessHolidayRepository;
    private $businessWeekendRepository;
    private $attendanceRepository;

    public function __construct(EloquentImplementation $business_holiday_repository, BusinessWeeklyRepositoryInterface $business_weekend_repository,AttendRepositoryInterface $attendance_repository)
    {
        $this->isCalculated = 0;
        $this->businessHolidayRepository = $business_holiday_repository;
        $this->businessWeekendRepository = $business_weekend_repository;
        $this->attendanceRepository = $attendance_repository;
    }

    public function setBusinessMember(BusinessMember $businessMember)
    {
        $this->businessMember = $businessMember;
        return $this;
    }


    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setTimeFrame(TimeFrame $timeFrame)
    {
        $this->timeFrame = $timeFrame;
        $this->workingDay = $this->timeFrame->start->daysInMonth;
        return $this;
    }


    /**
     * @return int
     */
    public function getPresent()
    {
        return (int)$this->present;
    }

    /**
     * @return int
     */
    public function getOnTime()
    {
        return (int)$this->onTime;
    }

    /**
     * @return int
     */
    public function getLate()
    {
        return (int)$this->late;
    }

    /**
     * @return int
     */
    public function getLeftEarly()
    {

        return (int)$this->leftEarly;
    }

    /**
     * @return int
     */
    public function getAbsent()
    {
        return (int)$this->absent;
    }

    /**
     * @return int
     */
    public function getWorkingDay()
    {
        return (int)$this->workingDay;
    }


    public function calculate()
    {
        $weekend_day = $this->businessWeekendRepository->where('business_id', $this->business->id)->get()->pluck('weekday_name')->toArray();
        $dates_of_holidays_formatted = $this->businessHolidayRepository->where('business_id', $this->business->id)
            ->where('start_date', '>=', Carbon::createFromDate(date('Y'), 1, 1))
            ->where('end_date', '<=', Carbon::createFromDate(date('Y'), 12, 31))
            ->get()
            ->map(function ($holiday) {
                return $holiday->start_date->format('Y-m-d');
            })->toArray();
        $this->attendances = $this->attendanceRepository->where('business_member_id', $this->businessMember->id)
            ->where('date', '>=', $this->timeFrame->start->toDateString())
            ->where('date', '<=', $this->timeFrame->end->toDateString())
            ->get();
        $period = CarbonPeriod::create($this->timeFrame->start, $this->timeFrame->end);
        foreach ($period as $date) {
            $is_weekend_or_holiday = $this->isWeekend($date, $weekend_day) || $this->isHoliday($date, $dates_of_holidays_formatted) ? 1 : 0;
            if ($is_weekend_or_holiday) $this->workingDay--;
            $attendance = $this->attendances->where('date', $date->toDateString())->first();
            if ($attendance) $this->incrementCounter($attendance->status);
            if (!$attendance && !$is_weekend_or_holiday) $this->absent++;
        }
        $this->isCalculated = 1;

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

    private function incrementCounter($status)
    {
        if ($status == Statuses::ON_TIME) $this->onTime++;
        if ($status == Statuses::LATE) $this->late++;
        if ($status == Statuses::LEFT_EARLY) $this->leftEarly++;
        if ($status == Statuses::ABSENT) $this->absent++;
    }
}