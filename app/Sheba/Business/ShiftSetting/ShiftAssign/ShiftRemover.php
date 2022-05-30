<?php namespace Sheba\Business\ShiftSetting\ShiftAssign;

use Sheba\Business\ShiftSetting\ShiftAssign\Requester;
use Sheba\Dal\ShiftAssignment\ShiftAssignmentRepository;

class ShiftRemover
{
    /*** @var ShiftAssignmentRepository  $shiftAssignmentRepository */
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
        $this->shiftAssignmentRepository->update($shift_calender, $data);
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
            'shift_settings' => NULL,
            'is_shift' => $this->shiftCalenderRequester->getIsShiftActivated(),
            'color_code' => $this->shiftCalenderRequester->getColorCode()
        ];
    }
}