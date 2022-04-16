<?php namespace Sheba\Business\Attendance\AttendanceTypes;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\AttendanceTypes\AttendanceErrorList;
use App\Sheba\Business\Attendance\AttendanceTypes\AttendanceType;
use App\Sheba\Business\Attendance\AttendanceTypes\GeoLocation;
use App\Sheba\Business\Attendance\AttendanceTypes\IPBased;
use App\Sheba\Business\Attendance\AttendanceTypes\Remote;

class TypeFactory
{
    /**
     * @param BusinessMember $business_member
     * @param $ip
     * @param $coords
     * @return AttendanceType
     */
    public static function create(BusinessMember $business_member, $ip, $coords)
    {
        /** @var Business $business */
        $business = $business_member->business;
        $isIpBasedAttendanceEnable = $business->isIpBasedAttendanceEnable();
        $isGeoLocationAttendanceEnable = $business->isGeoLocationAttendanceEnable();
        $isRemoteAttendanceEnable = $business->isRemoteAttendanceEnable($business_member->id);

        $checker = null;
        $next_checker = null;
        if ($isIpBasedAttendanceEnable) {
            $checker = $next_checker = new IPBased($business, $ip);
        }
        if ($isGeoLocationAttendanceEnable) {
            $geo_location = new GeoLocation($business, $coords);
            if (!$checker) $checker = $next_checker = $geo_location;
            else {
                $next_checker->setNext($geo_location);
                $next_checker = $geo_location;
            }
        }
        if ($isRemoteAttendanceEnable) {
            $remote = new Remote();
            if (!$checker) $checker = $next_checker = $remote;
            else {
                $next_checker->setNext($remote);
                $next_checker = $remote;
            }
        }
        $checker->setError(new AttendanceErrorList());
        return $checker;
    }
}
