<?php namespace Sheba\Business\AttendanceActionLog;


use App\Models\Business;
use Sheba\Dal\BusinessOfficeHours\Model;

class TimeByBusiness
{
    public function getOfficeStartTimeByBusiness()
    {
        return Model::where('business_id',$this->getBusiness()->id)->first()->start_time;
    }

    public function getOfficeEndTimeByBusiness()
    {
        return Model::where('business_id',$this->getBusiness()->id)->first()->end_time;
    }

    private function getBusiness()
    {
        $auth_info = \request()->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return Business::find($business_member['business_id']);
    }
}