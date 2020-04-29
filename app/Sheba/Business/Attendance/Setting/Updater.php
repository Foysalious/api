<?php namespace Sheba\Business\Attendance\Setting;

use App\Models\Business;
use App\Models\Member;
use Sheba\Dal\BusinessOffice\Contract as BusinessOfiiceRepoInterface;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;
    private $attendance_types;
    private $business_offices;
    private $business_office_repo;
    private $business;
    private $member;

    public function __construct(Business $business, BusinessOfiiceRepoInterface $business_office_repo, Member $member )
    {
        $this->attendance_types = $business->attendanceTypes()->withTrashed()->get();
        $this->business_offices = $business->offices()->withTrashed()->get();
        $this->business_office_repo = $business_office_repo;
        $this->business = $business;
        $this->member = $member;
    }

    public function updateAttendanceType($attendance_type_id, $action)
    {
        $attendance_type = $this->attendance_types->where('id',$attendance_type_id)->first();
        if($action == "checked")
        {
            if($attendance_type->trashed()) $attendance_type->restore();
        }
        if($action == "unchecked")
        {
            if(!$attendance_type->trashed()) $attendance_type->delete();
        }
        return true;
    }

    public function updateBusinessOffice($office_id, $office_name, $office_ip, $action)
    {
        $this->setModifier($this->member);
        if($office_id == "No ID")
        {
            if($action == "add")
            {
               $data = ["business_id" => $this->business->id, "name" => $office_name, "ip" => $office_ip];
               $this->business_office_repo->create($this->withCreateModificationField($data));
            }
        }
        else
        {
            $business_office = $this->business_offices->where('id',$office_id)->first();
            if($action == "edit")
            {
                $data = ["name" => $office_name, "ip" => $office_ip];
                $this->business_office_repo->update($business_office, $this->withUpdateModificationField($data));
            }
            if ($action == "delete")
            {
                $business_office->delete();
            }
        }
        return true;
    }
}
