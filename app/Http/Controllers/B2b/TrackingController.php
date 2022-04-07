<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Transformers\Business\LiveTrackingListTransformer;
use App\Transformers\Business\LiveTrackingEmployeeListsTransformer;
use App\Transformers\Business\LiveTrackingSettingChangeLogsTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Business\CoWorker\Filter\CoWorkerInfoFilter;
use Sheba\Business\LiveTracking\ChangeLogs\Creator as ChangeLogsCreator;
use Sheba\Business\LiveTracking\Employee\LiveTrackingDetails;
use Sheba\Business\LiveTracking\Employee\Updater as EmployeeSettingUpdater;
use Sheba\Business\LiveTracking\Updater as SettingsUpdater;
use Sheba\Dal\LiveTrackingSettings\LiveTrackingSettings;
use Sheba\Dal\TrackingLocation\TrackingLocationRepository;
use Sheba\ModificationFields;

class TrackingController extends Controller
{
    use ModificationFields;

    /*** @var TrackingLocationRepository */
    private $trackingLocation;

    public function __construct()
    {
        $this->trackingLocation = app(TrackingLocationRepository::class);
    }

    public function index(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        list($offset, $limit) = calculatePagination($request);
        $tracking_locations = $this->trackingLocation->select('business_id', 'business_member_id', 'location', 'log', 'date', 'time','created_at')
                            ->where('business_id', $business->id)
                            ->groupBy('business_member_id')
                            ->orderBy('created_at', 'DESC');
        if ($request->has('department')){
            $business_members = $business->getTrackLocationActiveBusinessMember();
            $business_members = $business_members->whereHas('role', function ($q) use ($request) {
                $q->whereHas('businessDepartment', function ($q) use ($request) {
                    $q->where('business_departments.id', $request->department);
                });
            })->pluck('id')->toArray();
            $tracking_locations = $tracking_locations->whereIn('business_member_id', $business_members);
        }
        $tracking_locations = $tracking_locations->get();
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($tracking_locations, new LiveTrackingListTransformer());
        $tracking_locations = $manager->createData($resource)->toArray()['data'];
        if ($request->has('search')) $tracking_locations = $this->searchWithEmployeeName(collect($tracking_locations), $request->search)->values();
        $total_count = count($tracking_locations);
        $tracking_locations = collect($tracking_locations)->splice($offset, $limit);
        return api_response($request, $tracking_locations, 200, ['total' => $total_count, 'tracking_locations' => $tracking_locations]);
    }

    public function settingsAction(Request $request, SettingsUpdater $updater, ChangeLogsCreator $change_logs_creator)
    {
        $this->validate($request, [
            'is_enable' => 'required|digits_between:0,1',
            'interval_time' => 'sometimes|required'
        ]);
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $this->setModifier($request->manager_member);
        $live_tracking_setting = $business->liveTrackingSettings;
        $previous_interval = $live_tracking_setting ? $live_tracking_setting->location_fetch_interval_in_minutes : null;
        $previous_is_enable = $live_tracking_setting ? $live_tracking_setting->is_enable : null;
        $live_tracking_setting = $updater->setBusiness($business)->setLiveTrackingSetting($live_tracking_setting)->setIsEnable($request->is_enable)->setIntervalTime($request->interval_time)->update();
        $change_logs_creator->setLiveTrackingSetting($live_tracking_setting)
            ->setIsEnable($request->is_enable)
            ->setIntervalTime($request->interval_time)
            ->setPreviousIsEnable($previous_is_enable)
            ->setPreviousIntervalTime($previous_interval)
            ->createBusinessSettingsLogs();
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getSettings(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var  LiveTrackingSettings $live_tracking_settings */
        $live_tracking_settings = $business->liveTrackingSettings;
        $tracking_settings = [
            'is_tracking_enable' => $live_tracking_settings->is_enable,
            'location_fetch_interval_in_minutes' => $live_tracking_settings->location_fetch_interval_in_minutes
        ];
        return api_response($request, null, 200, ['tracking_settings' => $tracking_settings]);
    }

    /**
     * @param Request $request
     * @param EmployeeSettingUpdater $employee_setting_updater
     * @return JsonResponse
     */
    public function employeeTrackingAction(Request $request, EmployeeSettingUpdater $employee_setting_updater)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $this->setModifier($request->manager_member);
        $employee_setting_updater->setBusiness($business)->setBusinessMember($request->employee)->setIsEnable($request->is_enable)->update();
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getChangesLogs(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $tracking_logs = $business->liveTrackingSettings;
        if (!$tracking_logs) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($tracking_logs->logs, new LiveTrackingSettingChangeLogsTransformer());
        $tracking_logs = $manager->createData($resource)->toArray()['data'];
        return api_response($request, $tracking_logs, 200, ['live_tracking_setting_changes_logs' => $tracking_logs]);
    }

    /**
     * @param $business_id
     * @param $business_member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function getTrackingDetails($business_id, $business_member_id, Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        /** @var BusinessMember $employee */
        $employee = BusinessMember::find((int)$business_member_id);
        $date = $request->date;
        $tracking_locations = $employee->liveLocationFilterByDate($date)->get();
        if (!$tracking_locations) return api_response($request, null, 404);

        $tracking_locations_details = (new LiveTrackingDetails($employee, $tracking_locations))->get();
        $tracking_locations_details['date'] = $date;
        return api_response($request, $tracking_locations_details, 200, ['live_tracking_details' => $tracking_locations_details]);
    }

    /**
     * @param Request $request
     * @param CoWorkerInfoFilter $co_worker_info_filter
     * @return JsonResponse
     */
    public function employeeLists(Request $request, CoWorkerInfoFilter $co_worker_info_filter)
    {
        /** @var Business $business */
        $business = $request->business;
        $business_members = $business->getActiveBusinessMember();
        list($offset, $limit) = calculatePagination($request);

        if ($request->has('department')) $business_members = $co_worker_info_filter->filterByDepartment($business_members, $request);
        if ($request->has('status')) $business_members = $business_members->where('is_live_track_enable', $request->status);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($business_members->get(), new LiveTrackingEmployeeListsTransformer());
        $employees = $manager->createData($resource)->toArray()['data'];

        if ($request->has('search')) $employees = $this->searchEmployee($employees, $request);

        $total_employees = count($employees);
        $limit = $this->getLimit($request, $limit, $total_employees);
        $employees = collect($employees)->splice($offset, $limit);

        if (count($employees) > 0) return api_response($request, $employees, 200, [
            'employees' => $employees,
            'total_employees' => $total_employees
        ]);
        return api_response($request, null, 404);
    }

    /**
     * @param $business_id
     * @param $business_member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function lastTrackedDate($business_id, $business_member_id, Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        /** @var BusinessMember $employee */
        $employee = BusinessMember::find((int)$business_member_id);
        $last_tracked = $employee->liveLocationFilterByDate()->first();

        if (!$last_tracked) return api_response($request, null, 404);
        $last_tracked_date = $last_tracked->date;
        $date_dropdown = $this->getDateDropDown($last_tracked_date);
        return api_response($request, null, 200, ['last-tracked' => $last_tracked_date, 'date-dropdown' => $date_dropdown]);
    }

    public function downloadLiveTrackingReport($business_id, $business_member_id, Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        /** @var BusinessMember $employee */
        $employee = BusinessMember::find((int)$business_member_id);
        $from_date = $request->from;
        $to_date = $request->to;
        $tracking_locations = $employee->liveLocationForADateRange($from_date, $to_date)->get();
    }

    /**
     * @param Request $request
     * @param $limit
     * @param $total_employees
     * @return mixed
     */
    private function getLimit(Request $request, $limit, $total_employees)
    {
        if ($request->has('limit') && $request->limit == 'all') return $total_employees;
        return $limit;
    }

    private function getDateDropDown($date)
    {
        $data = [];
        $date = Carbon::parse($date);
        for ($day = 1; $day <= 6; $day++) {
            $data[] = $date->subDay()->toDateString();
        }
        return $data;
    }

    private function searchWithEmployeeName($tracking_locations, $search_value)
    {
        return $tracking_locations->filter(function ($tracking_location) use ($search_value) {
            return str_contains(strtoupper($tracking_location['employee']['employee_name']), strtoupper($search_value));
        });
    }

    /**
     * @param $employees
     * @param Request $request
     * @return mixed
     */
    private function searchEmployee($employees, Request $request)
    {
        return $employees->filter(function ($employee) use ($request) {
            return str_contains(strtoupper($employee['name']), strtoupper($request->search));
        });
    }

}
