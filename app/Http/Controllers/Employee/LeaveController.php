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

use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use App\Sheba\Business\Leave\Creator as LeaveCreator;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;
use Throwable;
use DB;

class LeaveController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

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
            /*'note' => 'required', 'attachments.*' => 'file'*/
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $leave = $leave_creator->setTitle($request->title)
            ->setBusinessMember($business_member)
            ->setLeaveTypeId($request->leave_type_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date);
        #if ($request->attachments && is_array($request->attachments)) $leave_creator->setAttachments($request->attachments);
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
     * @param ApprovalRequestRepositoryInterface $approval_request_repository
     * @return JsonResponse
     */
    public function index(Request $request, LeaveRepoInterface $leave_repo, ApprovalRequestRepositoryInterface $approval_request_repository)
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
            $pending_approval_requests = $approval_request_repository->getPendingApprovalRequestByBusinessMember($business_member);
            return api_response($request, null, 200, [
                'leaves' => $leaves,
                'pending_approval_request' => $pending_approval_requests
            ]);
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
}
