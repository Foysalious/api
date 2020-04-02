<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\BusinessBasicInformation;
use App\Transformers\Business\ApprovalRequestTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;

class LeaveController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

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
        list($offset, $limit) = calculatePagination($request);
        $business_member = $request->business_member;
        $leaves = [];
        $leave_approval_requests = $approval_request_repo->getApprovalRequestByBusinessMemberFilterBy($business_member, Type::LEAVE);

        if ($request->has('status')) $leave_approval_requests = $leave_approval_requests->where('status', $request->status);
        if ($request->has('department')) $leave_approval_requests = $this->filterWithDepartment($leave_approval_requests, $request);
        if ($request->has('employee')) $leave_approval_requests = $this->filterWithEmployee($leave_approval_requests, $request);
        $total_leave_approval_requests = $leave_approval_requests->count();
        if ($request->has('limit')) $leave_approval_requests = $leave_approval_requests->splice($offset, $limit);

        foreach ($leave_approval_requests as $approval_request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var Member $member */
            $member = $requestable->businessMember->member;
            /** @var Profile $profile */
            $profile = $member->profile;
            /** @var BusinessRole $role */
            $role = $business_member->role;

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($approval_request, new ApprovalRequestTransformer($profile));
            $approval_request = $manager->createData($resource)->toArray()['data'];

            array_push($leaves, $approval_request);
        }

        if (count($leaves) > 0) return api_response($request, $leaves, 200, [
            'leaves' => $leaves,
            'total_leave_requests' => $total_leave_approval_requests,
        ]);
        else return api_response($request, null, 404);
    }

    private function filterWithDepartment($leave_approval_requests, Request $request)
    {
        return $leave_approval_requests->filter(function ($approval_request) use ($request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var BusinessMember $business_member */
            $business_member = $requestable->businessMember;
            /** @var BusinessRole $role */
            $role = $business_member->role;
            if ($role) return $role->businessDepartment->id == $request->department;
        });
    }

    private function filterWithEmployee($leave_approval_requests, Request $request)
    {
        return $leave_approval_requests->filter(function ($approval_request) use ($request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var Member $member */
            $member = $requestable->businessMember->member;
            return $member->id == $request->employee;
        });
    }

    /**
     * @param $approval_request
     * @param Request $request
     * @return void
     */
    public function show($approval_request, Request $request)
    {
        dd($approval_request);
    }
}