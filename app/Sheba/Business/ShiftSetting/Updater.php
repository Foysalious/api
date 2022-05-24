<?php namespace Sheba\Business\ShiftSetting;

use Carbon\Carbon;
use Sheba\Dal\ShiftCalender\ShiftCalenderRepository;

class Updater
{
    /** @var Requester $shiftRequester */
    private $shiftRequester;
    /*** @var ShiftCalenderRepository */
    private $shiftCalendarRepo;

    public function __construct()
    {
        $this->shiftCalendarRepo = app(ShiftCalenderRepository::class);
    }

    public function setShiftRequester(Requester $shiftRequester)
    {
        $this->shiftRequester = $shiftRequester;
        return $this;
    }

    public function updateColor()
    {
        $this->shiftRequester->getShift()->update(['color' => $this->shiftRequester->getColor()]);
    }

    public function update()
    {
        $this->shiftRequester->getShift()->update([
            'name' => $this->shiftRequester->getName(),
            'title' => $this->shiftRequester->getTitle(),
            'start_time' => $this->shiftRequester->getStartTime(),
            'end_time' => $this->shiftRequester->getEndTime(),
            'checkin_grace_enable' => $this->shiftRequester->getIsCheckInGraceAllowed(),
            'checkout_grace_enable' => $this->shiftRequester->getIsCheckOutGraceAllowed(),
            'checkin_grace_time' => $this->shiftRequester->getCheckinGraceTime(),
            'checkout_grace_time' => $this->shiftRequester->getCheckOutGraceTime(),
            'is_halfday_enable' => $this->shiftRequester->getIsHalfDayActivated(),
        ]);
        $this->updateShiftCalendar();
    }

    private function updateShiftCalendar()
    {
        $shift_assignments = $this->shiftCalendarRepo->where('shift_id', $this->shiftRequester->getShift()->id)->where('date', '>=', Carbon::now()->addDay())->get();
        foreach ($shift_assignments as $shift)
        {
            $shift->update([
                'name' => $this->shiftRequester->getName(),
                'start_time' => $this->shiftRequester->getStartTime(),
                'end_time' => $this->shiftRequester->getEndTime(),
                'is_half_day' => $this->shiftRequester->getIsHalfDayActivated()
            ]);
        }
    }

}
