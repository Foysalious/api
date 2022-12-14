<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\CoWorker\ManagerSubordinateEmployeeList;
use Carbon\Carbon;
use Sheba\Business\EmployeeTracking\EmployeeVisit\EmployeeVisitExcel;
use Maatwebsite\Excel\Facades\Excel as MaatwebsiteExcel;
use Sheba\Business\EmployeeTracking\MyVisit\MyVisitExcel;
use App\Transformers\Business\MyVisitListTransformer;
use App\Transformers\Business\TeamVisitListTransformer;
use App\Transformers\Business\VisitDetailsTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Sheba\Dal\Visit\VisitRepository;
use Illuminate\Support\Arr;
use Sheba\Helpers\TimeFrame;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VisitController extends Controller
{
    /** @var VisitRepository $visitRepository */
    private $visitRepository;
    /** @var ManagerSubordinateEmployeeList $subordinateEmployeeList */
    private $subordinateEmployeeList;

    /**
     * @param VisitRepository $visit_repository
     * @param ManagerSubordinateEmployeeList $subordinate_employee_list
     */
    public function __construct(VisitRepository $visit_repository, ManagerSubordinateEmployeeList $subordinate_employee_list)
    {
        $this->visitRepository = $visit_repository;
        $this->subordinateEmployeeList = $subordinate_employee_list;
    }

    /**
     * @param Request $request
     * @param TimeFrame $time_frame
     * @return JsonResponse|BinaryFileResponse
     */
    public function getTeamVisits(Request $request, TimeFrame $time_frame)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        list($offset, $limit) = calculatePagination($request);
        $visits = $this->visitRepository->getAllVisitsWithRelations()->where('visitor_id', '<>', $business_member->id)->orderBy('id', 'DESC');
        $visits = $visits->whereIn('visitor_id', $this->getBusinessMemberIds($business, $business_member));
        $show_empty_page = $visits->count() > 0 ? 0 : 1;

        /** Department Filter */
        if ($request->filled('department_id')) {
            $visits = $visits->whereHas('visitor', function ($q) use ($request) {
                $q->whereHas('role', function ($q) use ($request) {
                    $q->whereHas('businessDepartment', function ($q) use ($request) {
                        $q->where('business_departments.id', $request->department_id);
                    });
                });
            });
        }

        /** Status Filter */
        if ($request->filled('status')) {
            $visits = $visits->where('status', $request->status);
        }

        /** Month Filter */
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $time_frame = $time_frame->forDateRange($request->start_date, $request->end_date);
            $visits = $visits->whereBetween('schedule_date', [$time_frame->start, $time_frame->end]);
        }

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $visits = new Collection($visits->get(), new TeamVisitListTransformer());
        $visits = collect($manager->createData($visits)->toArray()['data']);

        if ($request->filled('search')) $visits = $this->searchWithEmployeeName($visits, $request);

        $visits = collect($visits);
        $total_visits = $visits->count();
        #$limit = $this->getLimit($request, $limit, $total_visits);
        if ($request->filled('limit') && !$request->filled('file')) $visits = $visits->splice($offset, $limit);

        if ($request->filled('file') && $request->file == 'excel') {
            $file_name = 'Employee_visit_report_'. Carbon::now()->timestamp;
            $excel = new EmployeeVisitExcel($visits->toArray());
            return MaatwebsiteExcel::download($excel, "$file_name.xlsx");
        }

        return api_response($request, $visits, 200, [
            'employees' => $visits,
            'total_visits' => $total_visits,
            'show_empty_page' => $show_empty_page
        ]);
    }

    /**
     * @param Request $request
     * @param TimeFrame $time_frame
     * @return JsonResponse|BinaryFileResponse
     */
    public function getMyVisits(Request $request, TimeFrame $time_frame)
    {
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        list($offset, $limit) = calculatePagination($request);
        $visits = $this->visitRepository->getAllVisitsWithRelations()->where('visitor_id', $business_member->id)->orderBy('id', 'DESC');
        $show_empty_page = $visits->count() > 0 ? 0 : 1;

        /** Status Filter */
        if ($request->filled('status')) {
            $visits = $visits->where('status', $request->status);
        }

        /** Month Filter */
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $time_frame = $time_frame->forDateRange($request->start_date, $request->end_date);
            $visits = $visits->whereBetween('schedule_date', [$time_frame->start, $time_frame->end]);
        }

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $visits = new Collection($visits->get(), new MyVisitListTransformer());
        $visits = collect($manager->createData($visits)->toArray()['data']);

        if ($request->filled('search')) $visits = $this->searchWithVisitTitle($visits, $request);

        $total_visits = $visits->count();
        #$limit = $this->getLimit($request, $limit, $total_visits);
        if ($request->filled('limit') && !$request->filled('file')) $visits = $visits->splice($offset, $limit);

        if ($request->filled('file') && $request->file == 'excel') {
            $file_name = 'My_visit_report_' . Carbon::now()->timestamp;
            $excel = new MyVisitExcel($visits->toArray());
            return MaatwebsiteExcel::download($excel, "$file_name.xlsx");
        }

        return api_response($request, $visits, 200, [
            'employees' => $visits,
            'total_visits' => $total_visits,
            'show_empty_page' => $show_empty_page
        ]);
    }

    /**
     * @param $business
     * @param $visit
     * @param Request $request
     * @return JsonResponse|void
     */
    public function show($business, $visit, Request $request)
    {
        $visit = $this->visitRepository->find($visit);
        if (!$visit) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($visit, new VisitDetailsTransformer());
        $visit = $manager->createData($resource)->toArray()['data'];

        if (count($visit) > 0) return api_response($request, $visit, 200, ['visit' => $visit]);
    }

    /**
     * @param Business $business
     * @param BusinessMember $business_member
     * @return array
     */
    private function getBusinessMemberIds(Business $business, BusinessMember $business_member)
    {
        if ($business_member->isSuperAdmin()) return $business->getActiveBusinessMember()->pluck('id')->toArray();
        $manager_subordinates = $this->subordinateEmployeeList->get($business_member);
        return Arr::pluck($manager_subordinates, 'id');
    }

    /**
     * @param $visits
     * @param Request $request
     * @return mixed
     */
    private function searchWithEmployeeName($visits, Request $request)
    {
        return $visits->filter(function ($visit) use ($request) {
            return str_contains(strtoupper($visit['profile']['name']), strtoupper($request->search));
        });
    }

    /**
     * @param $visits
     * @param Request $request
     * @return mixed
     */
    private function searchWithVisitTitle($visits, Request $request)
    {
        return $visits->filter(function ($visit) use ($request) {
            return str_contains(strtoupper($visit['title']), strtoupper($request->search));
        });
    }

    /**
     * @param $visits
     * @param Request $request
     * @return array
     */
    private function multipleSearch($visits, Request $request)
    {
        $visits = $visits->toArray();
        $employee_ids = array_filter($visits, function ($visit) use ($request) {
            return str_contains($visit['profile']['employee_id'], $request->search);
        });
        $employee_names = array_filter($visits, function ($visit) use ($request) {
            return str_contains(strtoupper($visit['profile']['name']), strtoupper($request->search));
        });
        $titles = array_filter($visits, function ($visit) use ($request) {
            return str_contains(strtoupper($visit['title']), strtoupper($request->search));
        });

        $searched_results = collect(array_merge($employee_ids, $employee_names, $titles));
        $searched_results = $searched_results->unique(function ($search) {
            return $search['id'];
        });
        return $searched_results->values()->all();
    }
}