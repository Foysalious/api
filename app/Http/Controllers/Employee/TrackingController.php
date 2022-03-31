<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Models\TrackingLocation;
use App\Sheba\Business\BusinessBasicInformation;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Sheba\Location\Geo;
use Sheba\Map\Client\BarikoiClient;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
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
            $date_time = $this->timeFormat($location['timestamp']);

            $data[] = [
                'business_id' => $business->id,
                'business_member_id' => $business_member->id,
                'location' => json_encode(['lat' => $geo->getLat(), 'lng' => $geo->getLng(), 'address' => $this->getAddress($geo)]),
                'log' => $location['log'],
                'date' => $date_time->toDateString(),
                'time' => $date_time->toTimeString(),
                'created_at' => $date_time->toDateTimeString()
            ];
        }
        TrackingLocation::insert($data);
        return api_response($request, null, 200);
    }

    public function getTrackingLocation(Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        if (!$request->date) return api_response($request, null, 404);
        $tracking_location = $business_member->liveLocationFilterByDate($request->date);

        return api_response($request, null, 200);
    }

    /**
     * @param $timestamp
     * @return string
     */
    private function timeFormat($timestamp)
    {
        $seconds = $timestamp / 1000;
        return Carbon::createFromTimestamp($seconds);
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