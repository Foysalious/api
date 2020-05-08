<?php namespace Sheba\Business;

use App\Sheba\Business\AttendanceType\InitialAttendanceTypeBusinessCommonInformationCreator;
use App\Sheba\Business\Holiday\InitialHolidayBusinessCommonInformationCreator;
use App\Sheba\Business\OfficeTiming\InitialOfficeTimeBusinessCommonInformationCreator;
use App\Sheba\Business\Weekend\InitialWeekendBusinessCommonInformationCreator;
use Sheba\ModificationFields;
use App\Models\Business;
use App\Models\Member;

class BusinessCommonInformationCreator
{
    use ModificationFields;

    /** @var InitialOfficeTimeBusinessCommonInformationCreator $officeHoursRepository */
    private $officeHoursCreator;
    /** @var InitialWeekendBusinessCommonInformationCreator $weekendCreator */
    private $weekendCreator;
    /** @var InitialAttendanceTypeBusinessCommonInformationCreator $attendanceTypesCreator */
    private $attendanceTypesCreator;
    /** @var InitialHolidayBusinessCommonInformationCreator $holidayCreator */
    private $holidayCreator;

    /** @var Business $business */
    private $business;
    /** @var Member $member */
    private $member;

    public function __construct(InitialOfficeTimeBusinessCommonInformationCreator $initialize_office_hour,
                                InitialWeekendBusinessCommonInformationCreator $initialize_weekend,
                                InitialAttendanceTypeBusinessCommonInformationCreator $initialize_attendance_types,
                                InitialHolidayBusinessCommonInformationCreator $initialize_holiday)
    {
        $this->officeHoursCreator = $initialize_office_hour;
        $this->weekendCreator = $initialize_weekend;
        $this->attendanceTypesCreator = $initialize_attendance_types;
        $this->holidayCreator = $initialize_holiday;
    }

    public function create(Business $business)
    {
        /** Business Office Hours */
        $this->officeHoursCreator->setBusiness($business)->create();
        /**  Business Weekend */
        $this->weekendCreator->setBusiness($business)->create();
        /** Business Attendance Type */
        $this->attendanceTypesCreator->setBusiness($business)->create();
        /** Business Government Holiday */
        $this->holidayCreator->setBusiness($business)->create();
    }
}

