<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\BusinessBasicInformation;
use App\Transformers\Business\CoWorkerMinimumTransformer;
use App\Transformers\BusinessEmployeeDetailsTransformer;
use App\Transformers\BusinessEmployeesTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionProcessor;
use Sheba\Business\CoWorker\ProfileCompletionCalculator;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use Sheba\Business\CoWorker\Requests\PersonalRequest;
use Sheba\Business\CoWorker\Statuses;
use Sheba\Business\CoWorker\UpdaterV2 as Updater;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\ModificationFields;
use Sheba\Repositories\ProfileRepository;
use App\Transformers\Business\EmployeeTransformer;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class EmployeeController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /** @var MemberRepositoryInterface $repo */
    private $repo;
    /** @var ApprovalRequestRepositoryInterface $approvalRequestRepo */
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
        $member = $business_member->member;

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($member, new EmployeeTransformer());
        $employee_details = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['info' => $employee_details]);
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
     * @param ProfileCompletionCalculator $completion_calculator
     * @return JsonResponse
     */
    public function getDashboard(Request $request, ActionProcessor $action_processor,
                                 ProfileCompletionCalculator $completion_calculator)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $member = $this->repo->find($business_member['member_id']);
        /** @var BusinessMember $business_member */
        $business_member = BusinessMember::find($business_member['id']);
        if (!$business_member) return api_response($request, null, 404);

        /** @var Attendance $attendance */
        $attendance = $business_member->attendanceOfToday();
        $checkout = $action_processor->setActionName(Actions::CHECKOUT)->getAction();

        $approval_requests = $this->approvalRequestRepo->getApprovalRequestByBusinessMember($business_member);
        $pending_approval_requests = $this->approvalRequestRepo->getPendingApprovalRequestByBusinessMember($business_member);
        $profile_completion_score = $completion_calculator->setBusinessMember($business_member)->getDigiGoScore();

        $data = [
            'id' => $member->id,
            'notification_count' => $member->notifications()->unSeen()->count(),
            'attendance' => [
                'can_checkin' => !$attendance ? 1 : ($attendance->canTakeThisAction(Actions::CHECKIN) ? 1 : 0),
                'can_checkout' => $attendance && $attendance->canTakeThisAction(Actions::CHECKOUT) ? 1 : 0,
                'is_note_required' => 0
            ],
            'is_approval_request_required' => $approval_requests->count() > 0 ? 1 : 0,
            'approval_requests' => ['pending_request' => $pending_approval_requests->count()],
            'is_profile_complete' => $profile_completion_score ? 1 : 0
        ];

        if ($data['attendance']['can_checkout']) {
            $data['attendance']['is_note_required'] = $checkout->isNoteRequired();
        }

        return api_response($request, $business_member, 200, ['info' => $data]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        /** @var Business $business */
        $business = Business::where('id', (int)$business_member['business_id'])->select('id', 'name', 'phone', 'email', 'type')->first();
        $business_members = $business->getAccessibleBusinessMember()->get();

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_members, new BusinessEmployeesTransformer());
        $employees_with_dept_data = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, [
            'employees' => $employees_with_dept_data['employees'],
            'departments' => $employees_with_dept_data['departments']
        ]);
    }

    /**
     * @param Request $request
     * @param $business_member_id
     * @return JsonResponse
     */
    public function show(Request $request, $business_member_id)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        /** @var Business $business */
        $business = Business::where('id', (int)$business_member['business_id'])->select('id', 'name', 'phone', 'email', 'type')->first();
        $business_member_with_details = $business->membersWithProfileAndAccessibleBusinessMember()->where('business_member.id', $business_member_id)->first();

        if (!$business_member_with_details) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_member_with_details, new BusinessEmployeeDetailsTransformer());
        $employee_details = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['details' => $employee_details]);
    }

    /**
     * @param Request $request
     * @param BasicRequest $basic_request
     * @param PersonalRequest $personalRequest
     * @param Updater $updater
     * @return JsonResponse
     */
    public function updateBasicInformation(Request $request, BasicRequest $basic_request, PersonalRequest $personalRequest, Updater $updater)
    {
        $validation_rules = [
            'name' => 'required|string',
            'department' => 'required|string',
            'designation' => 'required|string'
        ];

        if ($request->has('mobile')) $validation_rules['mobile'] = 'string|mobile:bd';
        $this->validate($request, $validation_rules);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->repo->find($business_member['member_id']);
        $this->setModifier($member);

        $request->mobile = ($request->has('mobile')) ? BDMobileFormatter::format($request->mobile) : null;
        $request->manager = ($request->has('manager')) ? $request->manager : null;
        $updater->setBusinessMember($business_member)
            ->setName($request->name)
            ->setMobile($request->mobile)
            ->setDepartment($request->department)
            ->setDesignation($request->designation)
            ->setManager($request->manager);

        if ($business_member->status == Statuses::INVITED)
            $updater->setStatus(Statuses::ACTIVE);

        if ($updater->hasError())
            return api_response($request, null, $updater->getErrorCode(), ['message' => $updater->getErrorMessage()]);

        $updater->update();

        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getManagersList(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        /** @var Business $business */
        $business = $this->getBusiness($request);

        $members = $business->membersWithProfileAndAccessibleBusinessMember();
        $members = $members->get()->unique();

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $employees = new Collection($members, new CoWorkerMinimumTransformer());
        $employees = collect($manager->createData($employees)->toArray()['data']);

        $employees = $employees->reject(function($employee) use ($business_member) { return $employee['id'] == $business_member->id; });

        if (count($employees) > 0) return api_response($request, $employees, 200, ['managers' => $employees->values()]);
        return api_response($request, null, 404);
    }
}
