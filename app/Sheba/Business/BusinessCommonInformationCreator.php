<?php namespace Sheba\Business;

use App\Models\BusinessDepartment;
use App\Models\BusinessRole;
use App\Models\BusinessSmsTemplate;
use App\Sheba\Business\AttendanceType\Creator as InitialAttendanceTypeBusinessCommonInformationCreator;
use App\Sheba\Business\Holiday\BusinessGovtHolidayCreator;
use App\Sheba\Business\OfficeTiming\Creator as InitialOfficeTimeBusinessCommonInformationCreator;
use App\Sheba\Business\Weekend\Creator as InitialWeekendBusinessCommonInformationCreator;
use Sheba\Business\AttendanceType\AttendanceType;
use Sheba\Business\LeaveType\DefaultType;
use Sheba\Business\OfficeTiming\OfficeTime;
use Sheba\ModificationFields;
use App\Models\Business;
use App\Models\Member;

use Sheba\Business\OfficeTiming\CreateRequest as OfficeTimingCreateRequest;
Use Sheba\Business\Weekend\CreateRequest as WeekendCreateRequest;
use Sheba\Business\AttendanceType\CreateRequest as AttendanceTypeCreateRequest;
use Sheba\Business\Holiday\CreateRequest as BusinessGovtHolidayCreatorRequest;

use Sheba\Business\LeaveType\Creator as LeaveTypeCreator;

class BusinessCommonInformationCreator
{
    use ModificationFields;

    /** @var InitialOfficeTimeBusinessCommonInformationCreator $officeHoursRepository */
    private $officeHoursCreator;
    /** @var InitialWeekendBusinessCommonInformationCreator $weekendCreator */
    private $weekendCreator;
    /** @var InitialAttendanceTypeBusinessCommonInformationCreator $attendanceTypesCreator */
    private $attendanceTypesCreator;
    /** @var BusinessGovtHolidayCreator $businessGovtHolidayCreator */
    private $businessGovtHolidayCreator;


    /** @var OfficeTimingCreateRequest $officeTimingCreateRequest */
    private $officeTimingCreateRequest;
    /** @var WeekendCreateRequest $weekendCreateRequest */
    private $weekendCreateRequest;
    /** @var AttendanceTypeCreateRequest $attendanceTypeCreateRequest */
    private $attendanceTypeCreateRequest;
    /** @var BusinessGovtHolidayCreatorRequest $businessGovtHolidayCreatorRequest */
    private $businessGovtHolidayCreatorRequest;

    /** @var LeaveTypeCreator $leaveTypeCreator */
    private $leaveTypeCreator;


    /** @var Business $business */
    public $business;
    /** @var Member $member */
    public $member;

    public function __construct(InitialOfficeTimeBusinessCommonInformationCreator $initialize_office_hour,
                                InitialWeekendBusinessCommonInformationCreator $initialize_weekend,
                                InitialAttendanceTypeBusinessCommonInformationCreator $initialize_attendance_types,
                                BusinessGovtHolidayCreator $initialize_holiday,
                                OfficeTimingCreateRequest $office_timing_create_request,
                                WeekendCreateRequest $weekend_create_request,
                                AttendanceTypeCreateRequest $attendance_type_create_request,
                                BusinessGovtHolidayCreatorRequest $business_govt_holiday_creator_request,
                                LeaveTypeCreator $leave_type_creator)
    {
        $this->officeHoursCreator = $initialize_office_hour;
        $this->weekendCreator = $initialize_weekend;
        $this->attendanceTypesCreator = $initialize_attendance_types;
        $this->businessGovtHolidayCreator = $initialize_holiday;

        $this->officeTimingCreateRequest = $office_timing_create_request;
        $this->weekendCreateRequest = $weekend_create_request;
        $this->attendanceTypeCreateRequest = $attendance_type_create_request;
        $this->businessGovtHolidayCreatorRequest = $business_govt_holiday_creator_request;

        $this->leaveTypeCreator = $leave_type_creator;
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
        /** Business Leave Types */
        foreach (DefaultType::getWithKeys() as $key => $value) {
            $this->leaveTypeCreator->setBusiness($this->business)
                ->setMember($this->member)
                ->setTitle($value)
                ->setTotalDays(DefaultType::getDays()[$key])
                ->create();
        }

        /** Business Office Hours */
        $this->officeTimingCreateRequest = $this->officeTimingCreateRequest->setBusiness($this->business)
            ->setStartTime(OfficeTime::START_TIME)
            ->setEndTime(OfficeTime::END_TIME);
        $this->officeHoursCreator->setOfficeTimingCreateRequest($this->officeTimingCreateRequest)->create();

        /**  Business Weekend */
        $weekdays = ['friday', 'saturday'];
        foreach ($weekdays as $weekday) {
            $this->weekendCreateRequest = $this->weekendCreateRequest->setBusiness($this->business)->setWeekday($weekday);
            $this->weekendCreator->setWeekendCreateRequest($this->weekendCreateRequest)->create();
        }

        /** Business Attendance Type */
        $this->attendanceTypeCreateRequest = $this->attendanceTypeCreateRequest->setBusiness($this->business)
            ->setAttendanceType(AttendanceType::ATTENDANCE_TYPE);
        $this->attendanceTypesCreator->setAttendanceTypeCreateRequest($this->attendanceTypeCreateRequest)->create();

        /** Business Government Holiday */
        $this->businessGovtHolidayCreatorRequest = $this->businessGovtHolidayCreatorRequest->setBusiness($this->business);
        $this->businessGovtHolidayCreator->setBusinessGovtHolidayCreatorRequest($this->businessGovtHolidayCreatorRequest)->create();

        $this->tagDepartment();
        $this->tagRole();
        $this->saveSmsTemplate();
    }

    private function tagDepartment()
    {
        $departments = ['IT', 'FINANCE', 'HR', 'ADMIN', 'MARKETING', 'OPERATION', 'CXO'];
        foreach ($departments as $department) {
            $dept = new BusinessDepartment();
            $dept->name = $department;
            $dept->business_id = $this->business->id;
            $dept->save();
        }
    }

    private function tagRole()
    {
        $roles = ['Manager', 'VP', 'Executive', 'Intern', 'Senior Executive', 'Driver'];
        $depts = BusinessDepartment::where('business_id', $this->business->id)->pluck('id')->toArray();
        foreach ($roles as $role) {
            foreach ($depts as $dept) {
                $b_role = new BusinessRole();
                $b_role->name = $role;
                $b_role->business_department_id = $dept;
                $b_role->save();
            }
        }
    }

    private function saveSmsTemplate()
    {
        $sms_template = new BusinessSmsTemplate();
        $sms_template->business_id = $this->business->id;
        $sms_template->event_name = "trip_request_accept";
        $sms_template->event_title = "Vehicle Trip Request Accept";
        $sms_template->template = "Your request for vehicle has been accepted. {{vehicle_name}} will be sent to you at {{arrival_time}}";
        $sms_template->variables = "vehicle_name;arrival_time";
        $sms_template->is_published = 1;
        $sms_template->save();
    }
}

