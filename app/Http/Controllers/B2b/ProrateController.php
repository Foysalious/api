<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Sheba\Business\Prorate\Updater;
use Sheba\Dal\BusinessMemberLeaveType\Contract as BusinessMemberLeaveTypeInterface;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use App\Http\Controllers\Controller;
use Sheba\Business\Prorate\Creator;
use Sheba\Business\Prorate\Requester as ProrateRequester;
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
        $business_departments = BusinessDepartment::published()->where('business_id', $business->id)
            ->select('id', 'business_id', 'name')->get();
        $department_info = [];
        foreach ($business_departments as $business_department) {
            $prorates = $this->businessMemberLeaveTypeRepo->builder()->with([
                'businessMember' => function ($q) {
                    $q->select('id', 'member_id', 'employee_id', 'business_role_id')
                        ->with([
                            'member' => function ($q) {
                                $q->select('id', 'profile_id')
                                    ->with([
                                        'profile' => function ($q) {
                                            $q->select('id', 'name', 'pro_pic');
                                        }]);
                            },
                            'role' => function ($q) {
                                $q->select('business_roles.id', 'business_department_id', 'name')->with([
                                    'businessDepartment' => function ($q) {
                                        $q->select('business_departments.id', 'business_id', 'name');
                                    }
                                ]);
                            }
                        ]);
                }, 'leaveType' => function ($query) {
                    $query->withTrashed()->select('id', 'business_id', 'title');
                }
            ]);
            $prorates = $prorates->whereHas('businessMember', function ($q) use ($business_department) {
                $q->whereHas('role', function ($q) use ($business_department) {
                    $q->whereHas('businessDepartment', function ($q) use ($business_department) {
                        $q->where('business_departments.id', $business_department->id);
                    });
                });
            });
            $prorates = $prorates->get();
            $employee_data = [];
            foreach ($prorates as $prorate) {
                $business_member = $prorate->businessMember;
                $member = $business_member->member;
                $profile = $member->profile;

                array_push($employee_data, [
                    'id' => $prorate->id,
                    'employee_id' => $business_member->employee_id,
                    'business_member_id' => $business_member->id,
                    'profile' => [
                        'id' => $profile->id,
                        'name' => $profile->name,
                        'pro_pic' => $profile->pro_pic,
                    ],
                    'leave_type_id' => $prorate->leaveType->id,
                    'leave_type' => $prorate->leaveType->title,
                    'total_days' => $prorate->total_days,
                    'note' => $prorate->note,
                    'department' => $business_department->name,
                ]);
            }
            array_push($department_info, [
                'department_id' => $business_department->id,
                'department' => $business_department->name,
                'employees' => $employee_data
            ]);
        }
        $department_info = collect($department_info);
        $department_info = $department_info->filter(function ($employee) use ($request) {
            return count($employee['employees']) > 0;
        })->values();

        if ($request->has('department')) {
            $department_info = $department_info->filter(function ($employee) use ($request) {
                return $employee['department_id'] == $request->department;
            })->values();
        }

        return api_response($request, null, 200, ['leave_prorate' => $department_info]);
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
        foreach ($request->business_member_leave_type_ids as $id) {
            /**@var BusinessMemberLeaveType $business_member_leave_type */
            $business_member_leave_type = $this->businessMemberLeaveTypeRepo->find($id);
            if (!$business_member_leave_type) {$not_found_counter++; continue;}
            $this->businessMemberLeaveTypeRepo->delete($business_member_leave_type);
        }
        $message = $not_found_counter > 0 ? 'One or more prorates have not found': null;
        return api_response($request, null, 200, ['message' => $message]);
    }
}