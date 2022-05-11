<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Sheba\Business\LiveTracking\DateDropDown;
use Illuminate\Support\Arr;
use Sheba\Dal\TrackingLocation\TrackingLocation;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\CoWorker\ManagerSubordinateEmployeeList;
use App\Transformers\CustomSerializer;
use App\Transformers\Employee\LiveTrackingLocationList;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Business\CoWorker\Filter\CoWorkerInfoFilter;
use Sheba\Location\Geo;
use Sheba\Map\Client\BarikoiClient;
use Sheba\ModificationFields;
use Throwable;

class TrackingController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /**
     * @param Request $request
     * @return JsonResponse
     */
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
            $geo = $this->getGeo($location);
            $date_time = $this->timeFormat($location['timestamp']);
            $data[] = [
                'business_id' => $business->id,
                'business_member_id' => $business_member->id,
                'location' => $geo ? json_encode(['lat' => $geo->getLat(), 'lng' => $geo->getLng(), 'address' => $this->getAddress($geo)]) : null,
                'log' => $location['log'],
                'date' => $date_time->toDateString(),
                'time' => $date_time->toTimeString(),
                'created_at' => $date_time->toDateTimeString()
            ];
        }

        TrackingLocation::insert($data);
        return api_response($request, null, 200);
    }

    /**
     * @param $business_member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function trackingLocationDetails($business_member_id, Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = BusinessMember::find((int)$business_member_id);

        if (!$business_member) return api_response($request, null, 404);

        if (!$request->date) return api_response($request, null, 404);
        $tracking_locations = $business_member->liveLocationFilterByDate($request->date)->get();

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($tracking_locations, new LiveTrackingLocationList());
        $tracking_locations = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['tracking_locations' => $tracking_locations]);
    }

    /**
     * @param Request $request
     * @param CoWorkerInfoFilter $co_worker_info_filter
     * @return JsonResponse
     */
    public function getManagerSubordinateList(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $managers = [];
        (new ManagerSubordinateEmployeeList())->getManager($business_member->id, $managers, $business_member->id);
        $managers_subordinate_ids = array_keys($managers);
        $business_members = BusinessMember::whereIn('id', $managers_subordinate_ids);

        if ($request->has('department')) {
            $business_members = $business_members->whereHas('role', function ($q) use ($request) {
                $q->whereHas('businessDepartment', function ($q) use ($request) {
                    $q->whereIn('business_departments.id', json_decode($request->department));
                });
            });
        }

        $data = [];
        foreach ($business_members->get() as $business_member) {
            $tracking_location = $business_member->liveLocationFilterByDate()->first();
            if (!$tracking_location) continue;

            $location = $tracking_location->location;
            $profile = $business_member->profile();
            /** @var BusinessRole $role */
            $role = $business_member->role;
            $data[] = [
                'business_member_id' => $business_member->id,
                'employee_id' => $business_member->employee_id,
                'business_id' => $tracking_location->business_id,
                'department_id' => $role ? $role->businessDepartment->id : null,
                'department' => $role ? $role->businessDepartment->name : null,
                'designation' => $role ? $role->name : null,
                'profile' => [
                    'id' => $profile->id,
                    'name' => $profile->name ?: null,
                    'pro_pic' => $profile->pro_pic
                ],
                'time' => Carbon::parse($tracking_location->time)->format('h:i a'),
                'location' => $location ? [
                    'lat' => $location->lat,
                    'lng' => $location->lng,
                    'address' => $location->address,
                ] : null,
                'last_activity_raw' => $tracking_location->created_at
            ];
        }

        if ($request->has('activity') && $request->activity != "null") $data = $this->getEmployeeOfNoActivityForCertainHour($data, $request->activity);
        if ($request->has('search')) $data = $this->searchEmployee($data, $request);
        $data = collect($data)->values();
        return api_response($request, null, 200, ['employee_list' => $data]);
    }

    /**
     * @param Request $request
     * @param DateDropDown $date_drop_down
     * @return JsonResponse
     */
    public function lastTrackedDate(Request $request, DateDropDown $date_drop_down)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $last_tracked_location = $business_member->liveLocationFilterByDate()->first();
        if (!$last_tracked_location) return api_response($request, null, 404);

        list($last_tracked_date, $date_dropdown) = $date_drop_down->getDateDropDown($last_tracked_location);
        return api_response($request, null, 200, ['last_tracked' => $last_tracked_date, 'date_dropdown' => $date_dropdown]);
    }

    /**
     * @return string
     */
    private function getAddress($geo)
    {
        try {
            return (new BarikoiClient)->getAddressFromGeo($geo)->getAddress();
        } catch (Throwable $exception) {
            return "";
        }
    }

    /**
     * @param $location
     * @return Geo|null
     */
    private function getGeo($location)
    {
        if ($this->isLatAvailable($location) && $this->isLngAvailable($location)) {
            $geo = new Geo();
            return $geo->setLat($location['lat'])->setLng($location['lng']);
        }
        return null;
    }

    /**
     * @param $location
     * @return bool
     */
    private function isLatAvailable($location)
    {
        if (isset($location['lat']) && !$this->isNull($location['lat'])) return true;
        return false;
    }

    /**
     * @param $location
     * @return bool
     */
    private function isLngAvailable($location)
    {
        if (isset($location['lng']) && !$this->isNull($location['lng'])) return true;
        return false;
    }

    /**
     * @param $data
     * @return bool
     */
    private function isNull($data)
    {
        if ($data == " ") return true;
        if ($data == "") return true;
        if ($data == 'null') return true;
        if ($data == null) return true;
        return false;
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
     * @param $tracking_locations
     * @param $activity
     * @return \Illuminate\Support\Collection
     */
    private function getEmployeeOfNoActivityForCertainHour($tracking_locations, $activity)
    {
        $from_time = Carbon::now()->subMinutes($activity);
        return collect($tracking_locations)->filter(function ($tracking_location) use ($from_time) {
            return $tracking_location['last_activity_raw'] <= $from_time;
        });
    }

    /**
     * @param $all_data
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    private function searchEmployee($all_data, Request $request)
    {
        return collect($all_data)->filter(function ($data) use ($request) {
            return str_contains(strtoupper($data['profile']['name']), strtoupper($request->search));
        });
    }
}
