<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Sheba\Business\ACL\AccessControl;
use App\Sheba\Business\Leave\Updater as LeaveUpdater;
use App\Transformers\Business\LeaveListTransformer;
use App\Transformers\Business\LeaveTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use App\Sheba\Business\Leave\Creator as LeaveCreator;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;

class LeaveController extends Controller
{
    use ModificationFields;

    public function getLeaveTypes(Request $request, LeaveTypesRepoInterface $leave_types_repo, LeaveRepoInterface $leave_repo, TimeFrame $time_frame)
    {
        try {
            $time_frame = $time_frame->forAYear(date('Y'));
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $leave_types = $leave_types_repo->getAllLeaveTypesByBusiness($business_member->business);
            $total_leaves_taken = $leave_repo->getTotalLeavesByBusinessMemberFilteredWithYear($business_member, $time_frame);
            foreach ($leave_types as $leave_type) {
                foreach ($total_leaves_taken as $leave) {
                    if($leave->leave_type_id == $leave_type->id) {
                        $leave_type->available_days = $leave_type->total_days - $leave->total_leaves_taken;
                    }
                }
            }
            return api_response($request, null, 200, ['leave_types' => $leave_types]);
        }
        catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    private function getBusinessMember(Request $request)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return BusinessMember::find($business_member['id']);
    }

    public function store(Request $request, LeaveCreator $leave_creator)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $leave = $leave_creator->setTitle($request->title)
                ->setBusinessMember($business_member)
                ->setLeaveTypeId($request->leave_type_id)
                ->setStartDate($request->start_date)
                ->setEndDate($request->end_date)
                ->setTotalDays()
                ->create();
            return api_response($request, null,200,['leave' => $leave->id]);
        }
        catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($leave, Request $request, LeaveRepoInterface $leave_repo)
    {
        try {
            $leave = $leave_repo->find($leave);
            $business_member = $this->getBusinessMember($request);
            if (!$leave || $leave->business_member_id != $business_member->id) return api_response($request, null, 403);
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Item($leave, new LeaveTransformer());
            return api_response($request, $leave, 200, ['leave' => $fractal->createData($resource)->toArray()['data']]);
        }
        catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function index(Request $request, LeaveRepoInterface $leave_repo)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $leaves = $leave_repo->getLeavesByBusinessMember($business_member);
            if ($request->has('type')) $leaves = $leaves->where('leave_type_id', $request->type);
            $leaves = $leaves->get();
            $fractal = new Manager();
            $resource = new Collection($leaves, new LeaveListTransformer());
            $leaves = $fractal->createData($resource)->toArray()['data'];
            return api_response($request, null, 200, ['leaves' => $leaves]);
        }
        catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateStatus($leave, Request $request, AccessControl $accessControl, LeaveUpdater $leaveUpdater)
    {
        $business_member = $this->getBusinessMember($request);
        $this->setModifier($business_member->member);
        $accessControl->setBusinessMember($business_member);
        if(!$accessControl->hasAccess('leave.rw')) return api_response($request, null, 403);
        $leave = Leave::findOrFail((int)$leave);
        $leaveUpdater->setLeave($leave)->setStatus($request->status)->updateStatus();
        return api_response($request, null, 200);
    }
}
