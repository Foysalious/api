<?php namespace App\Sheba\Business\OfficeSetting;

use Carbon\Carbon;
use Sheba\Dal\BusinessWeekendSettings\BusinessWeekendSettingsRepo;

class BusinessWeekendSettingsCreator
{
    /*** @var BusinessWeekendSettingsRepo $businessWeekendSettingsRepo*/
    private $businessWeekendSettingsRepo;
    private $business;
    private $weekend;

    public function __construct()
    {
        $this->businessWeekendSettingsRepo = app(BusinessWeekendSettingsRepo::class);
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setWeekend($weekend)
    {
        $this->weekend = $weekend;
        return $this;
    }

    public function create()
    {
        $this->businessWeekendSettingsRepo->create([
            'business_id' => $this->business->id,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'weekday_name' => json_encode(array_map('strtolower', $this->weekend))
        ]);
    }
}