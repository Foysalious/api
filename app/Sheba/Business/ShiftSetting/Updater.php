<?php namespace Sheba\Business\ShiftSetting;

class Updater
{
    /** @var Requester $shiftRequester */
    private $shiftRequester;

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
        
    }

}
