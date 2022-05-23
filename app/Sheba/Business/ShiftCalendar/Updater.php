<?php namespace Sheba\Business\ShiftCalendar;


use Sheba\Business\ShiftSetting\ShiftAssign\Requester;

class Updater
{
    /**
     * @var Requester $shiftCalenderRequester
     */
    private $shiftCalenderRequester;

    public function setShiftCalenderRequester(Requester $shiftCalenderRequester)
    {
        $this->shiftCalenderRequester = $shiftCalenderRequester;
        return $this;
    }

    public function update($shift)
    {
        $shift->update(['color' => $this->shiftCalenderRequester->getColorCode()]);
    }


}