<?php namespace Sheba\Business\ShiftSetting;


use Carbon\Carbon;
use Sheba\Dal\BusinessShift\BusinessShiftRepository;
use Sheba\Helpers\HasErrorCodeAndMessage;

class Requester
{
    use HasErrorCodeAndMessage;

    private $business;
    private $name;
    private $startTime;
    private $endTime;
    private $isCheckinGraceTimeAllowed;
    private $isCheckoutGraceTimeAllowed;
    private $checkInGraceTime;
    private $checkOutGraceTime;
    private $isHalfDayActivated;
    /*** @var BusinessShiftRepository  */
    private $businessShiftRepository;

    public function __construct()
    {
        $this->businessShiftRepository = app(BusinessShiftRepository::class);
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function getBusiness()
    {
        return $this->business;
    }

    public function setName($name)
    {
        $this->name = $name;
        $this->checkUniqueName();
        return  $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setStartTime($start_time)
    {
        $this->startTime = $start_time.':00';
        return $this;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setEndTime($end_time)
    {
        $this->endTime = $end_time.':59';
        $this->checkShiftDuration();
        return $this;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function setIsCheckInGraceAllowed($is_checkin_grace_time_allowed)
    {
        $this->isCheckinGraceTimeAllowed = $is_checkin_grace_time_allowed;
        return $this;
    }

    public function getIsCheckInGraceAllowed()
    {
        return $this->isCheckinGraceTimeAllowed;
    }

    public function setIsCheckOutGraceAllowed($is_checkout_grace_time_allowed)
    {
        $this->isCheckoutGraceTimeAllowed = $is_checkout_grace_time_allowed;
        return $this;
    }

    public function getIsCheckOutGraceAllowed()
    {
        return $this->isCheckoutGraceTimeAllowed;
    }

    public function setCheckInGraceTime($checkin_grace_time)
    {
        $this->checkInGraceTime = $checkin_grace_time;
        return $this;
    }

    public function getCheckinGraceTime()
    {
        return $this->checkInGraceTime;
    }

    public function setCheckOutGraceTime($checkout_grace_time)
    {
        $this->checkOutGraceTime = $checkout_grace_time;
        return $this;
    }

    public function getCheckOutGraceTime()
    {
        return $this->checkOutGraceTime;
    }

    public function setIsHalfDayActivated($is_half_day_activated)
    {
        $this->isHalfDayActivated = $is_half_day_activated;
        return $this;
    }

    public function getIsHalfDayActivated()
    {
        return $this->isHalfDayActivated;
    }

    private function checkUniqueName()
    {
        $existing_shift = $this->businessShiftRepository->where('business_id', $this->business->id)->where('name', $this->name)->first();
        if ($existing_shift) $this->setError(400, 'This shift name is already exists.');
    }

    private function checkShiftDuration()
    {
        $start_time = Carbon::parse($this->startTime);
        $end_time = Carbon::parse($this->endTime);
        $diff = $end_time->diffInHours($start_time);
        if ($diff < 2 || $diff > 24) $this->setError(400, 'Shift duration cannot be less than 2hrs or more than 24hrs .');
    }
}
