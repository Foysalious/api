<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Illuminate\Http\JsonResponse;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionChecker;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionProcessor;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Repositories\ProfileRepository;
use App\Transformers\Business\EmployeeTransformer;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class EmployeeController extends Controller
{
    private $repo;
    private $approvalRequestRepo;

    /**
     * EmployeeController constructor.
     * @param MemberRepositoryInterface $member_repository
     * @param ApprovalRequestRepositoryInterface $approval_request_repository
     */
    public function __construct(MemberRepositoryInterface $member_repository, ApprovalRequestRepositoryInterface $approval_request_repository)
    {
        $this->repo = $member_repository;
        $this->approvalRequestRepo = $approval_request_repository;
    }

    public function me(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        return api_response($request, null, 200, [
            'info' => (new EmployeeTransformer())->transform($this->repo->find($business_member['member_id']))
        ]);
    }

    public function updateMe(Request $request, ProfileRepository $profile_repo)
    {
        $this->validate($request, [
            'name' => 'string',
            'date_of_birth' => 'date',
            'profile_picture' => 'file',
            'gender' => 'in:Female,Male,Other',
            'address' => 'string',
            'blood_group' => 'in:' . implode(',', getBloodGroupsList(false)),
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $member = $this->repo->find($business_member['member_id']);

        $data = [];
        if ($request->has('name')) $data['name'] = $request->name;
        if ($request->has('date_of_birth')) $data['dob'] = $request->date_of_birth;
        if ($request->hasFile('profile_picture')) {
            $name = array_key_exists('name', $data) ? $data['name'] : $member->profile->name;
            $data['pro_pic'] = $profile_repo->saveProPic($request->profile_picture, $name);
        }
        if ($request->has('gender')) $data['gender'] = $request->gender;
        if ($request->has('address')) $data['address'] = $request->address;
        if ($request->has('blood_group')) $data['blood_group'] = $request->blood_group;

        $profile_repo->updateRaw($member->profile, $data);

        return api_response($request, null, 200);
    }

    public function updateMyPassword(Request $request, ProfileRepository $profile_repo)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password' => 'required|confirmed'
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->repo->find($business_member['member_id']);
        $profile = $member->profile;
        if (!password_verify($request->old_password, $profile->password)) {
            return api_response($request, null, 403, [
                'message' => "Old password does not match"
            ]);
        }
        $profile_repo->updatePassword($member->profile, $request->password);
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @param ActionProcessor $action_processor
     * @return JsonResponse
     */
    public function getDashboard(Request $request, ActionProcessor $action_processor)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->repo->find($business_member['member_id']);
        /** @var BusinessMember $business_member */
        $business_member = BusinessMember::find($business_member['id']);
        if (!$business_member) return api_response($request, null, 404);
        /** @var Attendance $attendance */
        $attendance = $business_member->attendanceOfToday();
        /** @var ActionChecker $checkout */
        $checkout = $action_processor->setActionName(Actions::CHECKOUT)->getAction();

        $approval_requests = $this->approvalRequestRepo->getApprovalRequestByBusinessMember($business_member);
        $pending_approval_requests = $this->approvalRequestRepo->getPendingApprovalRequestByBusinessMember($business_member);

        $data = [
            'id' => $member->id,
            'notification_count' => $member->notifications()->unSeen()->count(),
            'attendance' => [
                'can_checkin' => !$attendance ? 1 : ($attendance->canTakeThisAction(Actions::CHECKIN) ? 1 : 0),
                'can_checkout' => $attendance && $attendance->canTakeThisAction(Actions::CHECKOUT) ? 1 : 0,
                'is_note_required' => 0
            ],
            'is_approval_request_required' => $approval_requests->count() > 0 ? 1 : 0,
            'approval_requests' => ['pending_request' => $pending_approval_requests->count()]
        ];
        if ($data['attendance']['can_checkout']) $data['attendance']['is_note_required'] = $checkout->isNoteRequired($business_member);
        if ($business_member) return api_response($request, $business_member, 200, ['info' => $data]);
    }

    private function getBusinessMember(Request $request)
    {
        $auth_info = $request->auth_info;
        return $auth_info['business_member'];
    }
}
