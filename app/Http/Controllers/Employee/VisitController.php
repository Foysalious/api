<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Sheba\EmployeeTracking\Creator;
use App\Sheba\EmployeeTracking\Requester;
use App\Sheba\EmployeeTracking\Updater;
use App\Transformers\Business\CoWorkerManagerListTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\Visit\VisitRepository;
use Sheba\ModificationFields;
use Sheba\Repositories\Business\BusinessMemberRepository;

class VisitController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /*** @var BusinessMemberRepository $businessMemberRepository */
    private $businessMemberRepository;

    public function __construct(BusinessMemberRepository $business_member_repository)
    {
        $this->businessMemberRepository = $business_member_repository;
    }

    public function getCoWorkerManagerList(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $first_level_managers = $this->getCoWorkersUnderSpecificManager($business_member->id);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $managers_data = [];
        if ($first_level_managers->count() > 0)
            foreach ($first_level_managers as $first_level_manager) {
                $resource = new Item($first_level_manager, new CoWorkerManagerListTransformer());
                $managers_data[] = $manager->createData($resource)->toArray()['data'];
                $second_level_managers = $this->getCoWorkersUnderSpecificManager($first_level_manager->id);
                if ($second_level_managers->count() > 0)
                    foreach ($second_level_managers as $second_level_manager) {
                        $resource = new Item($second_level_manager, new CoWorkerManagerListTransformer());
                        $managers_data[] = $manager->createData($resource)->toArray()['data'];
                        $third_level_managers = $this->getCoWorkersUnderSpecificManager($second_level_manager->id);
                        if ($third_level_managers->count() > 0)
                            foreach ($third_level_managers as $third_level_manager) {
                                $resource = new Item($third_level_manager, new CoWorkerManagerListTransformer());
                                $managers_data[] = $manager->createData($resource)->toArray()['data'];
                            }
                    }
            }
        return api_response($request, null, 200, ['manager_list' => $managers_data]);
    }

    public function create(Request $request, Requester $requester, Creator $creator)
    {
        $this->validate($request, [
            'date' => 'required|date_format:Y-m-d H:i:s',
            'employee' => 'required|numeric',
            'title' => 'required|string',
            'description' => 'sometimes|required|string',
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->getMember($request);
        $this->setModifier($member);
        $requester->setBusinessMember($business_member)->setDate($request->date)->setEmployee($request->employee)->setTitle($request->title)->setDescription($request->description);
        $creator->setRequester($requester)->create();
        return api_response($request, null, 200);
    }

    public function update($visit_id, Request $request, Requester $requester, Updater $updater, VisitRepository $visit_repository)
    {
        $this->validate($request, [
            'date' => 'required|date_format:Y-m-d H:i:s',
            'employee' => 'required|numeric',
            'title' => 'required|string',
            'description' => 'sometimes|required|string',
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee_visit = $visit_repository->find($visit_id);
        if (!$employee_visit) return api_response($request, null, 404);
        $member = $this->getMember($request);
        $this->setModifier($member);
        $requester->setBusinessMember($business_member)->setEmployeeVisit($employee_visit)->setDate($request->date)->setEmployee($request->employee)->setTitle($request->title)->setDescription($request->description);
        $updater->setRequester($requester)->update();
        return api_response($request, null, 200);
    }

    private function getCoWorkersUnderSpecificManager($business_member_id)
    {
        return $this->businessMemberRepository->where('manager_id', $business_member_id)->get();
    }

    /**
     * @param Request $request
     * @param VisitRepository $visit_repository
     * @return \Illuminate\Http\JsonResponse
     */
    public function ownVisitList(Request $request, VisitRepository $visit_repository)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $own_visits = $visit_repository->where('visitor_id', $business_member->id)
                                       ->whereNotIn('status', ['completed', 'cancelled'])
                                       ->select('id', 'title', 'status', 'schedule_date')
                                       ->orderBy('id', 'desc')->get();

        $own_visits->map(function (&$own_visit) {
            $own_visit['date'] = Carbon::parse($own_visit->schedule_date)->format('M d, Y');
            return $own_visit;
        });
        return api_response($request, $own_visits, 200, ['own_visits' => $own_visits]);
    }

//    /**
//     * @param Request $request
//     * @param VisitRepository $visit_repository
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function ownVisitHistory(Request $request, VisitRepository $visit_repository)
//    {
//        $business_member = $this->getBusinessMember($request);
//        if (!$business_member) return api_response($request, null, 404);
//        $own_visits = $visit_repository->where('visitor_id', $business_member->id)
//                                       ->whereNotIn('status', ['completed', 'cancelled'])
//                                       ->select('id', 'title', 'status', 'schedule_date', DB::raw('YEAR(schedule_date) year, MONTH(schedule_date) month'))
//                                       ->orderBy('id', 'desc')->get();
//        $own_visits = $own_visits->groupBy('year')->transform(function($item, $k) {
//            return $item->groupBy('month');
//        });
//
//        $visit_history = [];
//        foreach ($own_visits as $key => $own_visit ) {
//           foreach ($own_visit as $visit_key => $visit) {
//               array_push($visit_history, [
//                  'year_month' => date("F", mktime(0, 0, 0, $visit_key, 1)).', '.$key,
//                  'total_visits' => $visit->count(),
//                  'visits' => $visit->toArray(),
//               ]);
//           }
//        }
//
//        return api_response($request, $own_visits, 200, ['own_visit_history' => $visit_history]);
//    }

}