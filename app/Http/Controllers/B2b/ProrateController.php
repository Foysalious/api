<?php namespace App\Http\Controllers\B2b;

use App\Models\Member;
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

    public function index(Request $request)
    {
        $prorates = $this->businessMemberLeaveTypeRepo->builder()->with([
            'businessMember' => function ($q) {
                $q->select('id', 'member_id', 'employee_id', 'business_role_id')
                    ->with([
                        'member' => function ($q) {
                            $q->select('id', 'profile_id')
                                ->with([
                                    'profile' => function ($q) {
                                        $q->select('id', 'name');
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
                $query->select('id', 'business_id', 'title');
            }
        ]);

        if ($request->has('department')) {
            $prorates = $prorates->whereHas('businessMember', function ($q) use ($request) {
                $q->whereHas('role', function ($q) use ($request) {
                    $q->whereHas('businessDepartment', function ($q) use ($request) {
                        $q->where('business_departments.id', $request->department);
                    });
                });
            });
        }

        $prorates = $prorates->get();
        $data = [];
        foreach ($prorates as $prorate) {
            $business_member = $prorate->businessMember;
            $member = $business_member->member;
            $profile = $member->profile;
            $department = $business_member->department();

            array_push($data, [
                'id' => $prorate->id,
                'employee_id' => $business_member->employee_id,
                'business_member_id' => $business_member->id,
                'profile' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                ],
                'department_id' => $department ? $department->id : null,
                'department' => $department ? $department->name : null
            ]);
        }

        return api_response($request, null, 200, ['leave_prorate' => $data]);
    }

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

    public function delete($business, Request $request)
    {
        $this->validate($request, [
            'business_member_leave_type_ids' => 'required|array',
        ]);
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        foreach ($request->business_member_leave_type_ids as $id) {
            /**@var BusinessMemberLeaveType $business_member_leave_type */
            $business_member_leave_type = $this->businessMemberLeaveTypeRepo->find($id);
            $this->businessMemberLeaveTypeRepo->delete($business_member_leave_type);
        }
        return api_response($request, null, 200);
    }
}