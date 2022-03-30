<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\TrackingLocation;
use App\Sheba\Business\BusinessBasicInformation;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Sheba\Location\Geo;
use Sheba\Map\Client\BarikoiClient;
use Sheba\ModificationFields;
use Throwable;

class TrackingController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    public function insertLocation(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $business = $this->getBusiness($request);
        $manager_member = $this->getMember($request);
        $this->setModifier($manager_member);

        $locations = $request->locations;
        $data = [];
        foreach ($locations as $location) {
            $geo = $this->getGeo($location['lat'], $location['lng']);
            $data[] = [
                'business_id' => $business->id,
                'business_member_id' => $business_member->id,
                'location' => json_encode(['lat' => $geo->getLat(), 'lng' => $geo->getLng(), 'address' => $this->getAddress($geo)]),
                'log' => $location['log'],
                'dateTime' => $this->timeFormat($location['datetime'])
            ];
        }

        TrackingLocation::insert($data);
        $req = $request->except('access_token', 'auth_user', 'auth_info', 'manager_member', 'business', 'business_member', 'token', 'profile');

        return api_response($request, null, 200, ['data' => $req]);
    }

    /**
     * @param $timestamp
     * @return string
     */
    private function timeFormat($timestamp)
    {
        $seconds = $timestamp / 1000;
        return Carbon::createFromTimestamp($seconds)->toDateTimeString();
    }


    /**
     * @return Geo|null
     */
    private function getGeo($lat, $lng)
    {
        if (!$lat || !$lng) return null;
        $geo = new Geo();
        return $geo->setLat($lat)->setLng($lng);
    }

    /**
     * @return string
     */
    public function getAddress($geo)
    {
        try {
            return (new BarikoiClient)->getAddressFromGeo($geo)->getAddress();
        } catch (Throwable $exception) {
            return "";
        }
    }
}