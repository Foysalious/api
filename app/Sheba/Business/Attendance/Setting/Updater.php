<?php namespace Sheba\Business\Attendance\Setting;

use App\Models\Business;
use App\Models\Member;
use Sheba\Dal\BusinessOffice\Contract as BusinessOfiiceRepoInterface;
use Sheba\Dal\BusinessAttendanceTypes\Contract as BusinessAttendanceTypesRepoInterface;
use Sheba\ModificationFields;
use Sheba\Business\Attendance\Setting\ActionType as Action;

class Updater
{
    use ModificationFields;

    private $attendance_types;
    private $business_offices;
    private $business_office_repo;
    private $attendance_type_repo;
    private $business;
    private $member;

    public function __construct(Business $business, Member $member, BusinessOfiiceRepoInterface $business_office_repo,
                                BusinessAttendanceTypesRepoInterface $attendance_type_repo)
    {
        $this->attendance_types = $business->attendanceTypes()->withTrashed()->get();
        $this->business_offices = $business->offices()->withTrashed()->get();
        $this->business_office_repo = $business_office_repo;
        $this->attendance_type_repo = $attendance_type_repo;
        $this->business = $business;
        $this->member = $member;
    }

    public function updateAttendanceType($attendance_type_id, $attendance_type, $action)
    {
        if ($attendance_type_id == "No ID" && $action == Action::CHECKED ) {
            $this->setModifier($this->member);
            $data = ["business_id" => $this->business->id, "attendance_type" => $attendance_type];
            $this->attendance_type_repo->create($this->withCreateModificationField($data));
        } else {
            $attendance_type = $this->attendance_types->where('id', $attendance_type_id)->first();
            if ($action == Action::CHECKED) {
                if ($attendance_type->trashed()) $attendance_type->restore();
            }
            if ($action == Action::UNCHECKED) {
                if (!$attendance_type->trashed()) $attendance_type->delete();
            }
        }
        return true;
    }

    public function updateBusinessOffice($office_id, $office_name, $office_ip, $action)
    {
        $this->setModifier($this->member);
        if ($office_id == "No ID") {
            if ($action == Action::ADD) {
                $data = ["business_id" => $this->business->id, "name" => $office_name, "ip" => $office_ip];
                $this->business_office_repo->create($this->withCreateModificationField($data));
            }
        } else {
            $business_office = $this->business_offices->where('id', $office_id)->first();
            if ($action == Action::EDIT) {
                $data = ["name" => $office_name, "ip" => $office_ip];
                $this->business_office_repo->update($business_office, $this->withUpdateModificationField($data));
            }
            if ($action == Action::DELETE) {
                $business_office->delete();
            }
        }
        return true;
    }
}
