<?php namespace Sheba\Business\ShiftSetting\ShiftAssign;

use Sheba\Business\ShiftSetting\ShiftAssign\Requester;
use Sheba\Dal\ShiftAssignment\ShiftAssignmentRepository;

class ShiftRemover
{
    /*** @var ShiftAssignmentRepository  $shiftAssignmentRepository */
    private $shiftAssignmentRepository;

    public function __construct(ShiftAssignmentRepository $shift_assignment_repository)
    {
        $this->shiftAssignmentRepository = $shift_assignment_repository;
    }

    /** @var Requester $shiftCalenderRequester */
    private $shiftCalenderRequester;

    public function setShiftCalenderRequester(Requester $shiftCalenderRequester)
    {
        $this->shiftCalenderRequester = $shiftCalenderRequester;
        return $this;
    }

    public function update($shift_calender)
    {
        $data = $this->makeData();
        foreach ($shift_calender as $calender_data) {
            $this->shiftAssignmentRepository->update($calender_data, $data);
        }
    }

    private function makeData()
    {
        return [
            'shift_id' => $this->shiftCalenderRequester->getShiftId(),
            'shift_name' => $this->shiftCalenderRequester->getShiftName(),
            'shift_title' => $this->shiftCalenderRequester->getShiftTitle(),
            'start_time' => $this->shiftCalenderRequester->getStartTime(),
            'end_time' => $this->shiftCalenderRequester->getEndTime(),
            'is_half_day' => $this->shiftCalenderRequester->getIsHalfDayActivated(),
            'is_general' => $this->shiftCalenderRequester->getIsGeneralActivated(),
            'is_unassigned' => $this->shiftCalenderRequester->getIsUnassignedActivated(),
            'shift_settings' => NULL,
            'is_shift' => $this->shiftCalenderRequester->getIsShiftActivated(),
            'color_code' => $this->shiftCalenderRequester->getColorCode()
        ];
    }
}
