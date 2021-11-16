<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Sheba\Business\Prorate\RunProrateOnActiveLeaveTypes;
use App\Sheba\Business\Prorate\AutoProrateCalculator;
use App\Transformers\Business\BusinessMemberLeaveProrateTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\Prorate\Updater;
use Sheba\Dal\BusinessMemberLeaveType\Contract as BusinessMemberLeaveTypeInterface;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use App\Http\Controllers\Controller;
use Sheba\Business\Prorate\Creator;
use Sheba\Business\Prorate\Requester as ProrateRequester;
use Sheba\Dal\LeaveType\Contract as LeaveTypeRepo;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class ProrateController extends Controller
{
    use ModificationFields;

    /** @var BusinessMemberLeaveTypeInterface $businessMemberLeaveTypeRepo */
    private $businessMemberLeaveTypeRepo;
    /** @var ProrateRequester $requester */
    private $requester;
    /** @var Creator $creator */
    private $creator;
    /**@var Updater $updater */
    private $updater;

    /**
     * ProrateController constructor.
     * @param ProrateRequester $prorate_requester
     * @param Creator $creator
     * @param Updater $updater
     * @param BusinessMemberLeaveTypeInterface $business_member_leave_type_repo
     */
    public function __construct(ProrateRequester $prorate_requester, Creator $creator, Updater $updater, BusinessMemberLeaveTypeInterface $business_member_leave_type_repo)
    {
        $this->requester = $prorate_requester;
        $this->creator = $creator;
        $this->updater = $updater;
        $this->businessMemberLeaveTypeRepo = $business_member_leave_type_repo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        list($offset, $limit) = calculatePagination($request);
        $business_members = $business->getActiveBusinessMember();
        if ($request->has('department_id')) {
            $business_members = $business_members->whereHas('role', function ($q) use ($request) {
                $q->whereHas('businessDepartment', function ($q) use ($request) {
                    $q->where('business_departments.id', $request->department_id);
                });
            });
        }
        if ($request->has('name')) {
            $business_members = $business_members->whereHas('member', function ($q) use ($request) {
                $q->whereHas('profile', function ($q) use ($request) {
                    $q->where('profiles.name', 'LIKE','%'.$request->name.'%');
                });
            });
        }
        $business_member_ids = $business_members->pluck('id')->toArray();
        $prorates = $this->businessMemberLeaveTypeRepo->getAllBusinessMemberProratesWithLeaveTypes($business_member_ids);
        if ($request->has('is_auto_prorated')) {
            $prorates = $prorates->where('is_auto_prorated', $request->is_auto_prorated);
        }
        if ($request->has('sort_column') && $request->sort_column == 'created_at') {
            $prorates = $prorates->OrderBy($request->sort_column, $request->sort_order);
        }
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $prorates = new Collection($prorates->get(), new BusinessMemberLeaveProrateTransformer());
        $prorates = collect($manager->createData($prorates)->toArray()['data']);
        if ($request->has('sort_column') && $request->sort_column !== 'created_at') $prorates = $this->sortByColumn($prorates, $request->sort_column, $request->sort_order)->values();
        $total_count = count($prorates);
        $prorates = collect($prorates)->splice($offset, $limit);
        return api_response($request, null, 200, ['total' => $total_count, 'leave_prorate' => $prorates]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'business_member_ids' => 'required|array',
            'leave_type_id' => 'required',
            'total_days' => 'required'
        ]);

        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $this->requester->setBusinessMemberIds($request->business_member_ids)
            ->setTotalDays($request->total_days)
            ->setLeaveTypeId($request->leave_type_id)
            ->setNote($request->note);

        $this->creator->setRequester($this->requester)->create();
        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $prorate
     * @param Request $request
     * @return JsonResponse
     */
    public function edit($business, $prorate, Request $request)
    {
        /**@var BusinessMemberLeaveType $business_member_leave_type */
        $business_member_leave_type = $this->businessMemberLeaveTypeRepo->find($prorate);
        $business_member_leave_type_by_type = null;
        if (!$business_member_leave_type) return api_response($request, null, 404);
        if ($request->leave_type_id != $business_member_leave_type->leave_type_id) $business_member_leave_type_by_type = $this->businessMemberLeaveTypeRepo->where('leave_type_id', $request->leave_type_id)->where('business_member_id', $business_member_leave_type->businessMember->id)->first();
        if ($business_member_leave_type_by_type) {
            $this->businessMemberLeaveTypeRepo->delete($business_member_leave_type);
            $business_member_leave_type = $business_member_leave_type_by_type;
        }
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $this->requester->setLeaveTypeId($request->leave_type_id)
            ->setTotalDays($request->total_days)
            ->setNote($request->note);

        $this->updater->setRequester($this->requester)->setBusinessMemberLeaveType($business_member_leave_type)->update();
        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function delete($business, Request $request)
    {
        $this->validate($request, [
            'business_member_leave_type_ids' => 'required|array',
        ]);
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        $not_found_counter = 0;
        $total_prorate = count($request->business_member_leave_type_ids);
        foreach ($request->business_member_leave_type_ids as $id) {
            /**@var BusinessMemberLeaveType $business_member_leave_type */
            $business_member_leave_type = $this->businessMemberLeaveTypeRepo->find($id);
            if (!$business_member_leave_type) {$not_found_counter++; continue;}
            $this->businessMemberLeaveTypeRepo->delete($business_member_leave_type);
        }
        if($not_found_counter === $total_prorate) return api_response($request, null, 404, ['message' => 'No prorates found']);
        $message = $not_found_counter > 0 ? 'One or more prorates not found.' : 'Successful';
        return api_response($request, null, 200, ['message' => $message]);
    }

    public function runAutoProrate(Request $request, AutoProrateCalculator $auto_prorate_calculator)
    {
        $this->validate($request, [
            'is_prorated' => 'required|string'
        ]);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 420);
        if ($request->is_prorated === 'no') return api_response($request, null, 200, ['message' => 'User does not want to prorate']);
        $business = $business_member->business;
        if (!$business->is_leave_prorate_enable) return api_response($request, null, 200, ['message' => 'Leave Prorate is deactivated for this business.']);
        $run_prorate_on_active_leaves = new RunProrateOnActiveLeaveTypes();
        $run_prorate_on_active_leaves->setBusiness($business)->run();
        return api_response($request, null, 200);
    }

    public function leaveTypeAutoProrate(Request $request, LeaveTypeRepo $leave_type_repo)
    {
        $this->validate($request, [
            'is_prorated' => 'required|string',
            'leave_type_id' => 'required'
        ]);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 420);
        if ($request->is_prorated === 'no') return api_response($request, null, 200, ['message' => 'User does not want to prorate']);
        $business = $business_member->business;
        if (!$business->is_leave_prorate_enable) return api_response($request, null, 200, ['message' => 'Leave Prorate is deactivated for this business.']);
        $leave_type = $leave_type_repo->find($request->leave_type_id);
        if (!$leave_type) return api_response($request, null, 404, ['message' => 'Sorry! Leave Type not found.']);
        $auto_prorate_calculator = new AutoProrateCalculator();
        $auto_prorate_calculator->setBusiness($business)->setLeaveType($leave_type)->run();
        return api_response($request, null, 200);
    }

    private function sortByColumn($data, $column, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($data)->$sort_by(function ($value, $key) use ($column) {
            return strtoupper($value['profile'][$column]);
        });
    }
}