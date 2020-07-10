<?php namespace Sheba\Business\Attendance\Type;

use App\Models\Business;
use Sheba\Dal\BusinessAttendanceTypes\Contract as BusinessAttendanceTypesRepoInterface;
use Sheba\Dal\BusinessAttendanceTypes\Model as BusinessAttendanceTypes;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    public $type;
    public $type_id;
    public $action;
    public $business;
    private $attendanceTypeRepo;

    /**
     * Updater constructor.
     * @param BusinessAttendanceTypesRepoInterface $attendance_type_repo
     */
    public function __construct(BusinessAttendanceTypesRepoInterface $attendance_type_repo)
    {
        $this->attendanceTypeRepo = $attendance_type_repo;
    }

    public function update()
    {
        if (is_null($this->type_id) && $this->action == AttendanceType::CHECKED) {
            $data = ['business_id' => $this->business->id, 'attendance_type' => $this->type];
            $this->attendanceTypeRepo->create($this->withCreateModificationField($data));
        } else {
            /** @var BusinessAttendanceTypes $attendance_type */
            $attendance_type = $this->attendanceTypeRepo->where('id', $this->type_id)->withTrashed()->first();
            if ($this->action == AttendanceType::CHECKED && $attendance_type->trashed()) $attendance_type->restore();
            if ($this->action == AttendanceType::UNCHECKED && !$attendance_type->trashed()) $attendance_type->delete();
        }
    }

    /**
     * @param mixed $type
     * @return Updater
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $type_id
     * @return Updater
     */
    public function setTypeId($type_id)
    {
        $this->type_id = $type_id;
        return $this;
    }

    /**
     * @param mixed $action
     * @return Updater
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param $business
     * @return mixed
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }
}
