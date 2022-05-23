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

    public function update()
    {
        $this->shiftRequester->getShift()->update(['color' => $this->shiftRequester->getColor()]);
    }

}