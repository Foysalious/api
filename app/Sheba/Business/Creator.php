<?php namespace Sheba\Business;

use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHoursRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\BusinessAttendanceTypes\Contract as BusinessAttendanceTypesRepoInterface;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\GovernmentHolidays\Contract as GovernmentHolidayRepoInterface;
use Sheba\ModificationFields;
use App\Models\Business;
use App\Models\Member;

class Creator
{
    use ModificationFields;

    /** @var BusinessOfficeHoursRepoInterface $officeHoursRepository */
    private $officeHoursRepository;
    /** @var BusinessWeekendRepoInterface $weekendRepository */
    private $weekendRepository;
    /** @var BusinessAttendanceTypesRepoInterface $attendanceTypesRepository */
    private $attendanceTypesRepository;
    /** @var BusinessHolidayRepoInterface $holidayRepository */
    private $holidayRepository;
    /** @var GovernmentHolidayRepoInterface $governmentHolidayRepository */
    private $governmentHolidayRepository;

    /** @var Business $business */
    private $business;
    /** @var Member $member */
    private $member;

    public function __construct(BusinessOfficeHoursRepoInterface $office_hours_repo,
                                BusinessWeekendRepoInterface $weekend_repo,
                                BusinessAttendanceTypesRepoInterface $attendance_types_repo,
                                BusinessHolidayRepoInterface $holiday_repo,
                                GovernmentHolidayRepoInterface $government_holidayRepo)
    {
        $this->officeHoursRepository = $office_hours_repo;
        $this->weekendRepository = $weekend_repo;
        $this->attendanceTypesRepository = $attendance_types_repo;
        $this->holidayRepository = $holiday_repo;
        $this->governmentHolidayRepository = $government_holidayRepo;
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
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    public function create()
    {
        $this->setModifier($this->member);

        /** Business Office Hours */
        $this->officeHoursRepository->create([
            'business_id' => $this->business->id,
            'start_time' => '09:00:59',
            'end_time' => '17:00:00',
        ]);

        /**  Business Weekend */
        $this->weekendRepository->create([
            'business_id' => $this->business->id,
            'weekday_name' => 'friday',
        ]);
        $this->weekendRepository->create([
            'business_id' => $this->business->id,
            'weekday_name' => 'saturday',
        ]);

        /** Business Attendance Type */
        $this->attendanceTypesRepository->create([
            'business_id' => $this->business->id,
            'attendance_type' => 'remote',
        ]);

        /** Business Government Holiday */
        $govt_holidays = $this->governmentHolidayRepository->builder()->select('id', 'title', 'start_date', 'end_date')->get();
        foreach ($govt_holidays as $govt_holiday) {
            $this->holidayRepository->create([
                'business_id' => $this->business->id,
                'title' => $govt_holiday->title,
                'start_date' => $govt_holiday->start_date,
                'end_date' => $govt_holiday->end_date,
            ]);
        }
    }
}

