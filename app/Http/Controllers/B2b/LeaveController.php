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
        $business_member = $this->getBusinessMember($request);
        $leaves = [];
        $approval_requests = $approval_request_repo->getApprovalRequestByBusinessMember($business_member);
        foreach ($approval_requests as $approval_request) {
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

        if (count($leaves) > 0) return api_response($request, $leaves, 200, ['leaves' => $leaves]);
        else return api_response($request, null, 404);
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