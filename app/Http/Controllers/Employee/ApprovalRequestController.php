<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Transformers\Business\ApprovalRequestTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Validation\ValidationException;
use Sheba\Business\ApprovalRequest\Updater;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;
use Throwable;

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
            /** @var BusinessRole $role */
            $role = $business_member->role;

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($approval_request, new ApprovalRequestTransformer($profile, $role));
            $approval_request = $manager->createData($resource)->toArray()['data'];

            array_push($approval_requests_list, $approval_request);
        }

        if (count($approval_requests_list) > 0) return api_response($request, $approval_requests_list, 200, [
            'request_lists' => $approval_requests_list,
            'type_lists' => [Type::LEAVE]
        ]);
        else return api_response($request, null, 404);
    }

    public function show($approval_request, Request $request)
    {
        $approval_request = $this->approvalRequestRepo->find($approval_request);
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        /** @var BusinessMember $business_member */
        $business_member = $requestable->businessMember;
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
        $role = $business_member->role;
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($approval_request, new ApprovalRequestTransformer($profile, $role));
        $approval_request = $manager->createData($resource)->toArray()['data'];
        $approvers = $this->getApprover($requestable);
        $approval_request = $approval_request + ['approvers' => $approvers];
        return api_response($request, null, 200, ['approval_details' => $approval_request]);
    }

    private function getApprover($requestable)
    {
        $approvers = [];
        foreach ($requestable->requests as $approval_request) {
            $business_member = $this->getBusinessMemberById($approval_request->approver_id);
            $member = $business_member->member;
            $profile = $member->profile;
            array_push($approvers, [
                'name' => $profile->name,
                'status' => $approval_request->status,
            ]);
        }
        return $approvers;
    }

    public function updateStatus(Request $request, Updater $updater)
    {
        try {
            $type = $request->type;
            $type_ids = json_decode($request->type_id);
            $business_member = $this->getBusinessMember($request);
            $member = $this->getMember($request);
            $requestable = 'Sheba\\Dal\\' . ucfirst(camel_case($type)) . '\\Model';
            $requestables = $requestable::whereIn('id', $type_ids)->get();
            foreach ($requestables as $requestable) {
                $approval_request = $requestable->requests->where('approver_id', $business_member->id)->first();
                if (!$approval_request)
                    continue;
                $updater->setMember($member)
                    ->setBusinessMember($business_member)
                    ->setApprovalRequest($approval_request);
                if ($error = $updater->hasError())
                    return api_response($request, $error, 400, ['message' => $error]);
                $updater->setStatus($request->status)->change();
                if ($requestable->status != 'rejected') {
                    $this->setModifier($member);
                    $rejected_approval_requests = $requestable->requests->where('status', 'rejected');
                    if ($rejected_approval_requests) {#Rejected Request
                        $requestable->update($this->withBothModificationFields(['status' => 'rejected']));
                    }
                    $accepted_approval_requests = $requestable->requests->whereIn('status', ['pending', 'rejected']);#All Status Accepted
                    if ($accepted_approval_requests->isEmpty()) {
                        $requestable->update($this->withBothModificationFields(['status' => 'accepted']));
                    }
                }
            }
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
