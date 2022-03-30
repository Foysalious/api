<?php namespace Sheba\Business\LiveTracking;

use App\Models\Business;

class Updater
{
    private $isEnable;
    /*** @var Business */
    private $business;

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setIsEnable($is_enable)
    {
        $this->isEnable = $is_enable;
        return $this;
    }

    public function update()
    {
        $this->business->update(['is_live_track_enable' => $this->isEnable]);
    }
}
