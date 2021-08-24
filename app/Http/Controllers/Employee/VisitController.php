<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Sheba\Business\CoWorker\ManagerSubordinateEmployeeList;
use App\Sheba\EmployeeTracking\Creator;
use App\Sheba\EmployeeTracking\Requester;
use App\Sheba\EmployeeTracking\Updater;
use App\Transformers\Business\AppVisitDetailsTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\Visit\VisitRepository;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\Business\EmployeeTracking\Visit\VisitList;
use Sheba\Business\EmployeeTracking\Visit\NoteCreator;
use Sheba\Business\EmployeeTracking\Visit\PhotoCreator;

class VisitController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getManagerSubordinateEmployeeList(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $managers_data = (new ManagerSubordinateEmployeeList())->get($business_member);
        return api_response($request, null, 200, ['manager_list' => $managers_data]);
    }

    /**
     * @param Request $request
     * @param Requester $requester
     * @param Creator $creator
     * @return JsonResponse
     */
    public function create(Request $request, Requester $requester, Creator $creator)
    {
        $this->validate($request, [
            'date' => 'required|date_format:Y-m-d H:i:s',
            'employee' => 'numeric',
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

    /**
     * @param $visit_id
     * @param Request $request
     * @param Requester $requester
     * @param Updater $updater
     * @param VisitRepository $visit_repository
     * @return JsonResponse
     */
    public function update($visit_id, Request $request, Requester $requester, Updater $updater, VisitRepository $visit_repository)
    {
        $this->validate($request, [
            'date' => 'required|date_format:Y-m-d H:i:s',
            'employee' => 'numeric',
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

    /**
     * @param Request $request
     * @param VisitRepository $visit_repository
     * @return JsonResponse
     */
    public function ownOngoingVisits(Request $request, VisitRepository $visit_repository)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $own_visits = $visit_repository->where('visitor_id', $business_member->id)
                                       ->whereNotIn('status', ['completed', 'cancelled'])
                                       ->select('id', 'title', 'status', 'schedule_date')
                                       ->orderBy('id', 'desc')->get();
        if (count($own_visits) == 0) return api_response($request, null, 404);
        $own_visits->map(function (&$own_visit) {
            $own_visit['date'] = Carbon::parse($own_visit->schedule_date)->format('M d, Y');
            return $own_visit;
        });
        return api_response($request, $own_visits, 200, ['own_visits' => $own_visits]);
    }

    /**
     * @param Request $request
     * @param VisitRepository $visit_repository
     * @param VisitList $visit_list
     * @return JsonResponse
     */
    public function ownVisitHistory(Request $request, VisitRepository $visit_repository, VisitList $visit_list)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $own_visits = $visit_repository->where('visitor_id', $business_member->id)
                                       ->whereIn('status', ['completed', 'cancelled'])
                                       ->select('id', 'title', 'status', 'start_date_time', 'end_date_time', 'total_time_in_minutes', 'schedule_date', DB::raw('YEAR(schedule_date) year, MONTH(schedule_date) month'))
                                       ->orderBy('id', 'desc')->get();
        if (count($own_visits) == 0) return api_response($request, null, 404);
        $own_visits = $own_visits->groupBy('year')->transform(function($item, $k) {
            return $item->groupBy('month');
        });

        $visit_history = $visit_list->getOwnVisitHistory($own_visits);
        return api_response($request, $own_visits, 200, ['own_visit_history' => $visit_history]);
    }

    /**
     * @param Request $request
     * @param VisitRepository $visit_repository
     * @param VisitList $visit_list
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function teamVisitsList(Request $request, VisitRepository $visit_repository, VisitList $visit_list, TimeFrame $time_frame)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $managers_data = (new ManagerSubordinateEmployeeList())->get($business_member);
        $business_member_ids = array_column($managers_data, 'id');

        $team_visits = $visit_list->getTeamVisits($visit_repository, $business_member_ids);

        if ($request->has('start_date') && $request->has('end_date')) {
            $time_frame = $time_frame->forDateRange($request->start_date, $request->end_date);
            $team_visits = $team_visits->whereBetween('schedule_date', [$time_frame->start, $time_frame->end]);
        }

        if ($request->has('employees')) {
            $team_visits = $team_visits->whereIn('visitor_id', json_decode($request->employees, 1));
        }

        if ($request->has('status')) {
            $team_visits = $team_visits->where('status', $request->status);
        }

        $team_visits = $team_visits->get();
        if (count($team_visits) == 0) return api_response($request, null, 404);
        $team_visits = $team_visits->groupBy('date');
        $team_visit_list = $visit_list->getTeamVisitList($team_visits);

        return api_response($request, $team_visit_list, 200, ['team_visit_list' => $team_visit_list]);
    }

    /**
     * @param Request $request
     * @param $visit
     * @param VisitRepository $visit_repository
     * @return JsonResponse
     */
    public function show(Request $request, $visit, VisitRepository $visit_repository)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $visit = $visit_repository->find($visit);
        if (!$visit) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($visit, new AppVisitDetailsTransformer());
        $visit = $manager->createData($resource)->toArray()['data'];

        return api_response($request, $visit, 200, ['visit' => $visit]);
    }

    /**
     * @param Request $request
     * @param $visit
     * @param VisitRepository $visit_repository
     * @param NoteCreator $note_creator
     * @return JsonResponse
     */
    public function storeNote(Request $request, $visit, VisitRepository $visit_repository, NoteCreator $note_creator)
    {
        $this->validate($request, [
            'date' => 'required|date_format:Y-m-d H:i:s',
            'note' => 'required|string',
            'status' => 'required|string'
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->getMember($request);
        $this->setModifier($member);

        $visit = $visit_repository->find($visit);
        if (!$visit) return api_response($request, null, 404);
        $note_creator->setVisit($visit)->setDate($request->date)
                     ->setNote($request->note)->setStatus($request->status)->store();
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @param $visit
     * @param VisitRepository $visit_repository
     * @param PhotoCreator $photo_creator
     * @return JsonResponse
     */
    public function storePhoto(Request $request, $visit, VisitRepository $visit_repository, PhotoCreator $photo_creator)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $this->validate($request, [
            'image' => 'file',
        ]);
        $member = $this->getMember($request);
        $this->setModifier($member);

        $visit = $visit_repository->find($visit);
        if (!$visit) return api_response($request, null, 404);
        $photo_creator->setVisit($visit)->setPhoto($request->image)->store();
        return api_response($request, null, 200);
    }

}