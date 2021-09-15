<?php namespace App\Http\Controllers\Employee;

use App\Transformers\Business\LeaveListTransformer;
use Exception;
use League\Fractal\Resource\Collection;
use Sheba\Business\LeaveRejection\Requester as LeaveRejectionRequester;
use App\Models\Attachment;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Transformers\AttachmentTransformer;
use App\Transformers\Business\ApprovalRequestTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Business\ApprovalRequest\UpdaterV2;
use Sheba\Business\LeaveRejection\Creator as LeaveRejectionCreator;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use App\Sheba\Business\BusinessBasicInformation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;
use Sheba\Dal\Leave\Status;

class ApprovalRequestController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    private $approvalRequestRepo;
    /** @var LeaveRejectionRequester $leaveRejectionRequester */
    private $leaveRejectionRequester;

    /**
     * ApprovalRequestController constructor.
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     * @param LeaveRejectionRequester $leave_rejection_requester
     */
    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo,LeaveRejectionRequester $leave_rejection_requester)
    {
        $this->approvalRequestRepo = $approval_request_repo;
        $this->leaveRejectionRequester = $leave_rejection_requester;
    }

    /**
     * @param Request $request
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     * @return JsonResponse
     */
    public function index(Request $request, ApprovalRequestRepositoryInterface $approval_request_repo)
    {
        $this->validate($request, ['type' => 'sometimes|string|in:' . implode(',', Type::get())]);
        /** @var Business $business */
        $business = $this->getBusiness($request);
        /**
         * @var BusinessMember $requester_business_member
         * means who is request for the approval list
         */
        $requester_business_member = $this->getBusinessMember($request);
        $approval_requests_list = [];

        list($offset, $limit) = calculatePagination($request);

        if ($request->has('type'))
            $approval_requests = $approval_request_repo->getApprovalRequestByBusinessMemberFilterBy($requester_business_member, $request->type);
        else
            $approval_requests = $approval_request_repo->getApprovalRequestByBusinessMember($requester_business_member);

        if ($request->has('limit')) $approval_requests = $approval_requests->splice($offset, $limit);

        foreach ($approval_requests as $approval_request) {
            if (!$approval_request->requestable) continue;
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var Member $member */
            $member = $requestable->businessMember->member;
            /** @var Profile $profile */
            $profile = $member->profile;

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($approval_request, new ApprovalRequestTransformer($profile, $business, $requester_business_member));
            $approval_request = $manager->createData($resource)->toArray()['data'];

            array_push($approval_requests_list, $approval_request);
        }

        return api_response($request, $approval_requests_list, 200, [
            'request_lists' => $approval_requests_list,
            'type_lists' => [Type::LEAVE]
        ]);
    }

    /**
     * @param $approval_request
     * @param Request $request
     * @return JsonResponse
     */
    public function show($approval_request, Request $request)
    {
        $approval_request = $this->approvalRequestRepo->find($approval_request);
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        /** @var Business $business */
        $business = $this->getBusiness($request);
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if ($business_member->id != $approval_request->approver_id)
            return api_response($request, null, 403, ['message' => 'You Are not authorized to show this request']);

        /** @var BusinessMember $leave_requester_business_member */
        $leave_requester_business_member = $requestable->businessMember;
        /** @var Member $member */
        $member = $leave_requester_business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
        $role = $leave_requester_business_member->role;

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($approval_request, new ApprovalRequestTransformer($profile, $business));
        $approval_request = $manager->createData($resource)->toArray()['data'];

        $attachments = $this->getAttachments($requestable);
        $approval_request = $approval_request + [
                'attachments' => $attachments,
                'department' => [
                    'department_id' => $role ? $role->businessDepartment->id : null,
                    'department' => $role ? $role->businessDepartment->name : null,
                    'designation' => $role ? $role->name : null
                ]
            ];

        return api_response($request, null, 200, ['approval_details' => $approval_request]);
    }

    /**
     * @param $requestable
     * @return array
     */
    private function getAttachments($requestable)
    {
        return $requestable->attachments->map(function (Attachment $attachment) {
            return (new AttachmentTransformer())->transform($attachment);
        })->toArray();
    }

    /**
     * @param Request $request
     * @param UpdaterV2 $updater
     * @return JsonResponse
     * @throws Exception
     */
    public function updateStatus(Request $request, UpdaterV2 $updater)
    {
        $validation_data = [
            'type' => 'required|string',
            'type_id' => 'required|string',
            'status' => 'required|string',
        ];
        if ($request->status == Status::REJECTED) $validation_data['reasons'] = 'required|string';
        $this->validate($request, $validation_data);

        /**
         *  $type leave, support, expense
         */
        $type = $request->type;
        /**
         * $type_ids Approval Request Ids
         */
        $type_ids = json_decode($request->type_id);

        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);

        $this->approvalRequestRepo->getApprovalRequestByIdAndType($type_ids, $type)
            ->each(function ($approval_request) use ($business_member, $updater, $request) {
                /** @var ApprovalRequest $approval_request */
                if ($approval_request->approver_id != $business_member->id) return;
                $this->leaveRejectionRequester->setNote($request->note)->setReasons($request->reasons);
                $updater->setBusinessMember($business_member)->setApprovalRequest($approval_request)->setLeaveRejectionRequester($this->leaveRejectionRequester);
                $updater->setStatus($request->status)->change();
            });

        return api_response($request, null, 200);
    }

    public function leaveHistory($business_member,Request $request, LeaveRepoInterface $leave_repo)
    {
        $business_member = $this->getBusinessMemberById($business_member);
        if (!$business_member) return api_response($request, null, 404);

        $leaves = $leave_repo->getLeavesByBusinessMember($business_member)->orderBy('id', 'desc');
        if ($request->has('type')) $leaves = $leaves->where('leave_type_id', $request->type);
        $leaves = $leaves->get();
        $fractal = new Manager();
        $resource = new Collection($leaves, new LeaveListTransformer());
        $leaves = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['leaves' => $leaves]);
    }
}

