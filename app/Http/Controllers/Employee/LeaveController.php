<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Sheba\Business\ACL\AccessControl;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\Leave\Updater as LeaveUpdater;
use App\Transformers\Business\LeaveListTransformer;
use App\Transformers\Business\LeaveTransformer;
use App\Transformers\CustomSerializer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use App\Sheba\Business\Leave\Creator as LeaveCreator;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;
use Throwable;
use DB;

class LeaveController extends Controller
{
    use ModificationFields;
    use BusinessBasicInformation;

    /**
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @param LeaveRepoInterface $leave_repo
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
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
                    if ($leave->leave_type_id == $leave_type->id) {
                        $leave_type->available_days = $leave_type->total_days - $leave->total_leaves_taken;
                    }
                }
            }
            return api_response($request, null, 200, ['leave_types' => $leave_types]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param Request $request
     * @param LeaveCreator $leave_creator
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request, LeaveCreator $leave_creator)
    {
        $this->validate($request, [
            'start_date' => 'required|before_or_equal:end_date', 'end_date' => 'required',
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $leave = $leave_creator->setTitle($request->title)
            ->setBusinessMember($business_member)
            ->setLeaveTypeId($request->leave_type_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date);

        if ($leave_creator->hasError())
            return api_response($request, null, $leave_creator->getErrorCode(), ['message' => $leave_creator->getErrorMessage()]);

        $leave = $leave->create();
        return api_response($request, null, 200, ['leave' => $leave->id]);
    }

    /**
     * @param $leave
     * @param Request $request
     * @param LeaveRepoInterface $leave_repo
     * @return JsonResponse
     */
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param Request $request
     * @param LeaveRepoInterface $leave_repo
     * @return JsonResponse
     */
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $leave
     * @param Request $request
     * @param AccessControl $accessControl
     * @param LeaveUpdater $leaveUpdater
     * @return JsonResponse
     */
    public function updateStatus($leave, Request $request, AccessControl $accessControl, LeaveUpdater $leaveUpdater)
    {
        $business_member = $this->getBusinessMember($request);
        $this->setModifier($business_member->member);
        $accessControl->setBusinessMember($business_member);
        if (!$accessControl->hasAccess('leave.rw')) return api_response($request, null, 403);
        $leave = Leave::findOrFail((int)$leave);
        $leaveUpdater->setLeave($leave)->setStatus($request->status)->updateStatus();
        return api_response($request, null, 200);
    }

    private function getMyApprovalRequest(BusinessMember $business_member)
    {
        return ApprovalRequest::where('approver_id', $business_member->id)->where('status', 'pending')->count();
    }

    public function getMyLeaves(Request $request)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            $member = $this->getMember($request);
            list($offset, $limit) = calculatePagination($request);
            $leaves = Leave::query()->select('id', 'business_member_id', 'leave_type_id', 'status', 'total_days', 'created_at', DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
                ->with('leaveType')->where('business_member_id', $business_member->id)->skip($offset)->take($limit)->groupby('created_at')->orderBy('created_at', 'desc');
            if ($request->has('type')) {
                $leaves = $leaves->where('leave_type_id', $request->type);
            }
            $all_leaves = [];
            foreach ($leaves->get() as $leave) {
                $leave_type = $leave->leaveType;
                array_push($all_leaves, [
                    'id' => $leave->id,
                    'total_days' => $leave->total_days,
                    'status' => $leave->status,
                    'leave_type' => [
                        'id' => $leave_type->id,
                        'title' => $leave_type->title
                    ],
                    'created_at' => $leave_type->created_at ? $leave_type->created_at->format('M d, Y') : null,
                    'month' => $leave->month,
                    'year' => $leave->year,
                ]);
            }
            $approval_requests = $this->getMyApprovalRequest($business_member);
            return api_response($request, null, 200, [
                'all_leaves' => $all_leaves,
                'pending_approval_request' => $approval_requests
            ]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
