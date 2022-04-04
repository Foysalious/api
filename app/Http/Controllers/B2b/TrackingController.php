<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\TrackingLocation;
use App\Transformers\Business\LiveTrackingListTransformer;
use App\Transformers\Business\LiveTrackingEmployeeListsTransformer;
use App\Transformers\Business\LiveTrackingSettingChangeLogsTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Business\CoWorker\Filter\CoWorkerInfoFilter;
use Sheba\Business\LiveTracking\ChangeLogs\Creator as ChangeLogsCreator;
use Sheba\Business\LiveTracking\Employee\Updater as EmployeeSettingUpdater;
use Sheba\Business\LiveTracking\Updater as SettingsUpdater;
use Sheba\Dal\LiveTrackingSettings\LiveTrackingSettings;
use Sheba\ModificationFields;

class TrackingController extends Controller
{
    use ModificationFields;

    public function index(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $tracking_locations = TrackingLocation::select('business_id', 'business_member_id', 'location', 'log', 'date', 'time','created_at')->where('business_id', $business->id)->groupBy('business_member_id')->orderBy('created_at', 'DESC')->get();
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($tracking_locations, new LiveTrackingListTransformer());
        $tracking_locations = $manager->createData($resource)->toArray()['data'];
        return api_response($request, $tracking_locations, 200, ['tracking_locations' => $tracking_locations]);
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

    public function getTrackingDetails(Request $request)
    {
        $data = [
            'date' => '2022-04-05',
            'employee' => [
                'name' => 'Asad Ahmed',
                'employee_id' => "737",
                'department' => "IT",
                'designation' => "Software Engineer"
            ],
            'timeline' => [
                [
                    'time' => '9:10 AM',
                    'location' => [
                        'lat' => 23.2929292,
                        'lng' => 90.8787484,
                        'address' => 'Sheba.xyz'
                    ]
                ],
                [
                    'time' => '9:10 AM',
                    'location' => [
                        'lat' => 23.2929292,
                        'lng' => 90.8787484,
                        'address' => 'Sheba.xyz'
                    ]
                ]
            ]
        ];
        return api_response($request, $data, 200, ['live_tracking_details' => $data]);
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

}
