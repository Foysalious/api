<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Transformers\Business\ApprovalRequestTransformer;
use App\Transformers\CustomSerializer;

use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use App\Sheba\Business\BusinessBasicInformation;
use Sheba\Business\ApprovalRequest\Updater;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;

class ApprovalRequestController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    private $approvalRequestRepo;

    /**
     * ApprovalRequestController constructor.
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     */
    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo)
    {
        $this->approvalRequestRepo = $approval_request_repo;
    }

    /**
     * @param Request $request
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     * @return JsonResponse
     */
    public function index(Request $request, ApprovalRequestRepositoryInterface $approval_request_repo)
    {
        $this->validate($request, ['type' => 'sometimes|string|in:' . implode(',', Type::get())]);
        $business_member = $this->getBusinessMember($request);
        $approval_requests_list = [];

        if ($request->has('type'))
            $approval_requests = $approval_request_repo->getApprovalRequestByBusinessMemberFilterBy($business_member, $request->type);
        else
            $approval_requests = $approval_request_repo->getApprovalRequestByBusinessMember($business_member);

        foreach ($approval_requests as $approval_request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var Member $member */
            $member = $requestable->businessMember->member;
            /** @var Profile $profile */
            $profile = $member->profile;

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($approval_request, new ApprovalRequestTransformer($profile));
            $approval_request = $manager->createData($resource)->toArray()['data'];

            array_push($approval_requests_list, $approval_request);
        }

        if (count($approval_requests_list) > 0) return api_response($request, $approval_requests_list, 200, [
            'request_lists' => $approval_requests_list,
            'type_lists' => [Type::LEAVE]
        ]);
        else return api_response($request, null, 404);
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

        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if ($business_member->id != $approval_request->approver_id)
            return api_response($request, null, 403, ['message' => 'You Are not authorized to show this request']);

        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
        $role = $business_member->role;

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($approval_request, new ApprovalRequestTransformer($profile));
        $approval_request = $manager->createData($resource)->toArray()['data'];

        $approvers = $this->getApprover($requestable);
        $approval_request = $approval_request + [
            'approvers' => $approvers,
            'department' => [
                'department_id' => $role ? $role->businessDepartment->id : null,
                'department'    => $role ? $role->businessDepartment->name : null,
                'designation'   => $role ? $role->name : null
            ]
        ];

        return api_response($request, null, 200, ['approval_details' => $approval_request]);
    }

    /**
     * @param $requestable
     * @return array
     */
    private function getApprover($requestable)
    {
        $approvers = [];
        foreach ($requestable->requests as $approval_request) {
            $business_member = $this->getBusinessMemberById($approval_request->approver_id);
            $member = $business_member->member;
            $profile = $member->profile;
            array_push($approvers, ['name' => $profile->name, 'status' => $approval_request->status]);
        }
        return $approvers;
    }

    /**
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function updateStatus(Request $request, Updater $updater)
    {
        $this->validate($request, [
            'type' => 'required|string',
            'type_id' => 'required|string',
            'status' => 'required|string',
        ]);

        $type = $request->type;
        $type_ids = json_decode($request->type_id);

        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);

        $this->approvalRequestRepo->getApprovalRequestByIdAndType($type_ids, $type)
            ->each(function ($approval_request) use ($business_member, $updater, $request) {
                /** @var ApprovalRequest $approval_request */
                if ($approval_request->approver_id != $business_member->id) return;
                $updater->setBusinessMember($business_member)->setApprovalRequest($approval_request);

                /*if ($error = $updater->hasError())
                    return api_response($request, $error, 400, ['message' => $error]);*/

                $updater->setStatus($request->status)->change();
            });

        return api_response($request, null, 200);
    }
}
