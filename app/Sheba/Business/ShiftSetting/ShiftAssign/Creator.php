<?php namespace Sheba\Business\ShiftSetting\ShiftAssign;

use Sheba\Dal\ShiftAssignment\ShiftAssignmentRepository;

class Creator
{
    /*** @var ShiftAssignmentRepository  $shiftAssignmentRepository*/
    private $shiftAssignmentRepository;

    public function __construct(ShiftAssignmentRepository $shiftAssignmentRepository)
    {
        $this->shiftAssignmentRepository = $shiftAssignmentRepository;
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
        foreach ($shift_calender as $calender_data)
        {
            $this->shiftAssignmentRepository->update($calender_data, $data);
        }
    }

    private function makeData()
    {
        return [
            'shift_id' => $this->shiftCalenderRequester->getShiftId(),
            'shift_name' => $this->shiftCalenderRequester->getShiftName(),
            'start_time' => $this->shiftCalenderRequester->getStartTime(),
            'end_time' => $this->shiftCalenderRequester->getEndTime(),
            'is_half_day' => $this->shiftCalenderRequester->getIsHalfDayActivated(),
            'is_general' => $this->shiftCalenderRequester->getIsGeneralActivated(),
            'shift_settings' => $this->shiftCalenderRequester->getShiftSettings(),
            'is_unassigned' => $this->shiftCalenderRequester->getIsUnassignedActivated(),
            'is_shift' => $this->shiftCalenderRequester->getIsShiftActivated(),
            'color_code' => $this->shiftCalenderRequester->getColorCode()
        ];
    }
}
