<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\CoWorker\ProfileInformation\EmergencyInfoUpdater;
use App\Sheba\Business\CoWorker\ProfileInformation\EmployeeType;
use App\Sheba\Business\CoWorker\ProfileInformation\OfficialInfoUpdater;
use App\Sheba\Business\CoWorker\ProfileInformation\PersonalInfoUpdater;
use App\Sheba\Business\CoWorker\ProfileInformation\ProfileRequester;
use App\Sheba\Business\CoWorker\ProfileInformation\ProfileUpdater;
use Sheba\Dal\LiveTrackingSettings\LiveTrackingSettings;
use Sheba\Gender\Gender;
use App\Transformers\Business\CoWorkerMinimumTransformer;
use App\Transformers\Business\EmergencyContactInfoTransformer;
use App\Transformers\Business\FinancialInfoTransformer;
use App\Transformers\Business\OfficialInfoTransformer;
use App\Transformers\Business\PersonalInfoTransformer;
use App\Transformers\BusinessEmployeeDetailsTransformer;
use App\Transformers\BusinessEmployeesTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use Intervention\Image\Image;
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
use Sheba\Dal\Visit\Status;
use Sheba\Dal\Visit\VisitRepository;
use Sheba\Dal\BusinessMemberBadge\BusinessMemberBadgeRepository;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\ModificationFields;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\OAuth2\AuthUser;
use Sheba\OAuth2\SomethingWrongWithToken;
use Sheba\OAuth2\WrongPinError;
use Sheba\Repositories\ProfileRepository;
use App\Transformers\Business\EmployeeTransformer;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Throwable;
use Illuminate\Support\Facades\Cache;

class EmployeeController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /** @var MemberRepositoryInterface $repo */
    private $repo;
    /** @var ApprovalRequestRepositoryInterface $approvalRequestRepo */
    private $approvalRequestRepo;
    /** @var AccountServer $accounts */
    private $accounts;
    /*** @var BusinessMember */
    private $businessMember;
    /*** @var ProfileRequester $profileRequester */
    private $profileRequester;
    /** @var BusinessMemberBadgeRepository $badgeRepo */
    private $badgeRepo;

    /**
     * EmployeeController constructor.
     * @param MemberRepositoryInterface $member_repository
     * @param ApprovalRequestRepositoryInterface $approval_request_repository
     * @param AccountServer $accounts
     */
    public function __construct(MemberRepositoryInterface          $member_repository,
                                ApprovalRequestRepositoryInterface $approval_request_repository,
                                AccountServer                      $accounts)
    {
        $this->repo = $member_repository;
        $this->approvalRequestRepo = $approval_request_repository;
        $this->accounts = $accounts;
        $this->businessMember = app(BusinessMember::class);
        $this->profileRequester = app(ProfileRequester::class);
        $this->badgeRepo = app(BusinessMemberBadgeRepository::class);
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
            'gender' => 'in:' . Gender::implodeEnglish(),
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
     * @param VisitRepository $visit_repository
     * @return JsonResponse
     */
    public function getDashboard(Request                     $request, ActionProcessor $action_processor,
                                 ProfileCompletionCalculator $completion_calculator, VisitRepository $visit_repository)
    {
        /** @var Business $business */
        $business = $this->getBusiness($request);
        if (!$business) return api_response($request, null, 404);
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $member = $this->getMember($request);
        $department = $business_member->department();
        $profile = $business_member->profile();
        $designation = $business_member->role()->first();

        /** @var Attendance $attendance */
        $attendance = $business_member->attendanceOfToday();
        /** @var Attendance $last_attendance */
        $last_attendance = $business_member->lastAttendance();
        $last_attendance_log = $last_attendance ? $last_attendance->actions()->get()->sortByDesc('id')->first() : null;
        $is_note_required = 0;
        $note_action = null;
        if ($last_attendance_log && !$last_attendance_log['note']) {
            $note_data = $this->checkNoteRequired($last_attendance, $last_attendance_log, $action_processor);
            $is_note_required = $note_data['is_note_required'];
            $note_action = $note_data['note_action'];
        }
        $approval_requests = $this->approvalRequestRepo->getApprovalRequestByBusinessMember($business_member);
        $pending_approval_requests_count = $this->approvalRequestRepo->countPendingLeaveApprovalRequests($business_member);
        $profile_completion_score = $completion_calculator->setBusinessMember($business_member)->getDigiGoScore();
        $single_pending_visit = $visit_repository->getFirstPendingVisit($business_member->id);
        $pending_visit_count = $visit_repository->getPendingVisitCount($business_member->id);
        $current_visit = $visit_repository->getCurrentVisit($business_member->id);
        $today_visit_count = $visit_repository->getTodayVisitCount($business_member->id);
        $is_badge_seen = (int)$this->badgeRepo->isBadgeSeenOnCurrentMonth($business_member->id);
        $is_manager = (int)$business_member->isManager();

        /** @var  LiveTrackingSettings $live_tracking_settings */
        $live_tracking_settings = $business->liveTrackingSettings;
/*
        $today_shift = $business_member->shiftToday();
        $next_shift =$business_member->nextShift();
        $today_shift_start_time = Carbon::createFromFormat('Y-m-d H:i:s', $today_shift->date.' '.$today_shift->start_time);
        $today_shift_end_time = Carbon::createFromFormat('Y-m-d H:i:s', $today_shift->date.' '.$today_shift->end_time);
        $next_shift_start_time = Carbon::createFromFormat('Y-m-d H:i:s', $next_shift->date.' '.$next_shift->start_time);
        $next_shift_end_time = Carbon::createFromFormat('Y-m-d H:i:s', $next_shift->date.' '.$next_shift->end_time);
        $diff = $today_shift_start_time->diffInHours(Carbon::parse($next_shift_start_time));
        if ( $diff < 16) {
            $adjacent_shift_avg_time = $diff / 2;
            $can_check_in = !$attendance && ($today_shift_end_time->addHours($adjacent_shift_avg_time) < Carbon::now()) ? 1 : 0;
            $can_check_out = $attendance && ($today_shift_end_time->addHours($adjacent_shift_avg_time) < Carbon::now()) ? 1 : 0;
        }else if ($diff >= 16) {
            $can_check_in = !$attendance && $today_shift_end_time->addHours(8) < Carbon::now() ? 1 : 0;
            $can_check_out = $attendance && $today_shift_end_time->addHours(8) < Carbon::now() ? 1 : 0;
        }
        dd($can_check_in, $can_check_out);*/

        $data = [
            'id' => $member->id,
            'business_member_id' => $business_member->id,
            'department_id' => $department ? $department->id : null,
            'notification_count' => $member->notifications()->unSeen()->count(),
            'attendance' => [
                'can_checkin' => !$attendance ? 1 : ($attendance->canTakeThisAction(Actions::CHECKIN) ? 1 : 0),
                'can_checkout' => $attendance && $attendance->canTakeThisAction(Actions::CHECKOUT) ? 1 : 0,
            ],
            'note_data' => [
                'date' => $last_attendance ? Carbon::parse($last_attendance['date'])->format('jS F Y') : null,
                'is_note_required' => $is_note_required,
                'note_action' => $note_action
            ],
            'is_remote_enable' => $business->isRemoteAttendanceEnable($business_member->id),
            'is_approval_request_required' => $approval_requests->count() > 0 ? 1 : 0,
            'approval_requests' => ['pending_request' => $pending_approval_requests_count],
            'is_profile_complete' => $profile_completion_score ? 1 : 0,
            'is_eligible_for_lunch' => in_array($business->id, config('b2b.BUSINESSES_IDS_FOR_LUNCH')) ? [
                'link' => config('b2b.BUSINESSES_LUNCH_LINK'),
            ] : null,
            'is_sheba_platform' => in_array($business->id, config('b2b.BUSINESSES_IDS_FOR_REFERRAL')) ? 1 : 0,
            'is_payroll_enable' => $business->payrollSetting->is_enable,
            'is_enable_employee_visit' => $business->is_enable_employee_visit,
            'pending_visit_count' => $pending_visit_count,
            'today_visit_count' => $today_visit_count,
            'single_visit' => $pending_visit_count === 1 ? [
                'id' => $single_pending_visit->id,
                'title' => $single_pending_visit->title
            ] : null,
            'currently_on_visit' => $current_visit ? $current_visit->id : null,
            'is_badge_seen' => $is_badge_seen,
            'is_manager' => $is_manager,
            'user_profile' => [
                'name' => $profile->name ?: null,
                'pro_pic' => $profile->pro_pic ?: null,
                'designation' => $designation ? ucwords($designation->name) : null
            ],
            'is_live_track_enable' => $business_member->is_live_track_enable,
            'location_fetch_interval_in_minutes' => $live_tracking_settings ? $live_tracking_settings->location_fetch_interval_in_minutes : null
        ];

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
        $business = $this->getBusiness($request);
        $employees_with_dept_data = $this->lazyLoadingStrategy($business, $request);
        return api_response($request, null, 200, [
            'employees' => $employees_with_dept_data['employees'],
            'departments' => $employees_with_dept_data['departments']
        ]);
    }

    /**
     * @param $business
     * @param $request
     * @return mixed
     */
    public function lazyLoadingStrategy($business, $request)
    {
        $cache_key = 'phonebook:' . (int)$business->id;
        return Cache::store('redis')->remember($cache_key, 5, function () use ($business, $request) {
            $business_members = $this->accessibleBusinessMembers($business, $request);
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($business_members->get(), new BusinessEmployeesTransformer());
            return $manager->createData($resource)->toArray()['data'];
        });
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

        $business_members = $business->getActiveBusinessMember()->where('id', '<>', $business_member->id);

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $employees = new Collection($business_members->get(), new CoWorkerMinimumTransformer());
        $employees = collect($manager->createData($employees)->toArray()['data']);

        if (count($employees) > 0) return api_response($request, $employees, 200, ['managers' => $employees->values()]);
        return api_response($request, null, 404);
    }

    /**
     * @param Request $request
     * @param ProfileRepository $profile_repo
     * @return JsonResponse
     * @throws SomethingWrongWithToken
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking|WrongPinError
     */
    public function login(Request $request, ProfileRepository $profile_repo)
    {
        $this->validate($request, ['email' => 'required', 'password' => 'required']);

        $profile = $profile_repo->checkExistingEmail($request->email);
        if (!$profile) return response()->json(['code' => 404, 'message' => 'Profile not found']);

        /** @var Member $member */
        $member = $profile->member;
        if (!$member) return api_response($request, null, 420, ['message' => 'You are not eligible employee']);
        /** @var BusinessMember $business_member */
        $business_member = $member->businessMember;
        if (!$business_member) return api_response($request, null, 420, ['message' => 'You are not eligible employee']);

        $token = $this->accounts->getTokenByEmailAndPasswordV2($request->email, $request->password);
        $auth_user = AuthUser::createFromToken($token);
        $business = $this->business($auth_user);

        $info = [
            'token' => $token,
            'user' => [
                'name' => $auth_user->getName(),
                'mobile' => $profile->mobile,
                'image' => $profile->pro_pic,
                'business_id' => $business ? $business->id : null,
                'business_name' => $business ? $business->name : null,
                'is_remote_attendance_enable' => $business->isRemoteAttendanceEnable()
            ]
        ];

        return api_response($request, null, 200, $info);
    }

    /**
     * @param $auth_user
     * @return null
     */
    private function business($auth_user)
    {
        $business_id = $auth_user->getMemberAssociatedBusinessId();
        return $business_id ? Business::find($business_id) : null;
    }

    /**
     * @param Business $business
     * @param Request $request
     * @return mixed
     */
    private function accessibleBusinessMembers(Business $business, Request $request)
    {
        if ($request->has('for') && $request->for == 'phone_book') return $business->getActiveBusinessMember();
        return $business->getAccessibleBusinessMember();
    }

    /**
     * @param $business_member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function getFinancialInfo($business_member_id, Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee = $this->businessMember->find($business_member_id);
        if (!$employee) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($employee, new FinancialInfoTransformer());
        $employee_financial_details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['financial_info' => $employee_financial_details]);
    }

    public function getOfficialInfo($business_member_id, Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee = $this->businessMember->find($business_member_id);
        if (!$employee) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($employee, new OfficialInfoTransformer());
        $employee_official_details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['official_info' => $employee_official_details]);
    }

    public function updateEmployee($business_member_id, Request $request, ProfileUpdater $profile_updater)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string',
            'department' => 'required|string',
            'designation' => 'required|string',
            'joining_date' => 'required|date',
            'gender' => 'required|string|in:' . Gender::implodeEnglish()
        ]);

        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $member = $this->getMember($request);
            $this->setModifier($member);

            $this->profileRequester->setBusinessMember($business_member)
                ->setName($request->name)
                ->setEmail($request->email)
                ->setDepartment($request->department)
                ->setDesignation($request->designation)
                ->setJoiningDate($request->joining_date)
                ->setGender($request->gender);

            if ($this->profileRequester->hasError()) return api_response($request, null, $this->profileRequester->getErrorCode(), ['message' => $this->profileRequester->getErrorMessage()]);

            $profile_updater->setProfileRequester($this->profileRequester)->update();

            return api_response($request, null, 200);
        } catch (Throwable $e) {
            return api_response($request, null, 420, ['message' => 'You are not eligible employee']);
        }
    }

    public function updateOfficialInfo($business_member_id, Request $request, OfficialInfoUpdater $official_info_updater)
    {
        $this->validate($request, [
            'manager' => 'required|numeric',
            'employee_type' => 'required|string|in:' . implode(',', EmployeeType::get()),
            'employee_id' => 'required',
            'grade' => 'required'
        ]);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee = $this->businessMember->find($business_member_id);
        if (!$employee) return api_response($request, null, 404);
        $member = $this->repo->find($business_member['member_id']);
        $this->setModifier($member);

        $this->profileRequester
            ->setBusinessMember($employee)
            ->setManager($request->manager)
            ->setEmployeeType($request->employee_type)
            ->setEmployeeId($request->employee_id)
            ->setGrade($request->grade);

        $official_info_updater->setProfileRequester($this->profileRequester)->update();

        return api_response($request, null, 200);

    }

    public function updateEmergencyInfo($business_member_id, Request $request, EmergencyInfoUpdater $emergency_info_updater)
    {
        $this->validate($request, [
            'name' => 'sometimes|required|string',
            'mobile' => 'sometimes|required|mobile:bd',
            'relationship' => 'sometimes|required|string',
        ]);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee = $this->businessMember->find($business_member_id);
        if (!$employee) return api_response($request, null, 404);
        $member = $this->repo->find($business_member['member_id']);
        $this->setModifier($member);

        $this->profileRequester
            ->setBusinessMember($employee)
            ->setEmergencyContactName($request->name)
            ->setEmergencyContactMobile($request->mobile)
            ->setEmergencyContactRelation($request->relationship);

        $emergency_info_updater->setProfileRequester($this->profileRequester)->update();

        return api_response($request, null, 200);

    }

    public function getEmergencyContactInfo($business_member_id, Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee = $this->businessMember->find($business_member_id);
        if (!$employee) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($employee, new EmergencyContactInfoTransformer());
        $employee_emergency_details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['emergency_contact_info' => $employee_emergency_details]);
    }

    public function getPersonalInfo($business_member_id, Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee = $this->businessMember->find($business_member_id);
        if (!$employee) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($employee, new PersonalInfoTransformer());
        $employee_emergency_details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['emergency_contact_info' => $employee_emergency_details]);
    }

    public function updatePersonalInfo($business_member_id, Request $request, PersonalInfoUpdater $personal_info_updater)
    {
        $validation_data = [
            'mobile' => 'mobile:bd',
            'dob' => 'date',
        ];

        $validation_data['nid_front'] = $this->isFile($request->nid_front) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';
        $validation_data['nid_back'] = $this->isFile($request->nid_back) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';
        $validation_data['passport_image'] = $this->isFile($request->passport_image) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';

        $this->validate($request, $validation_data);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee = $this->businessMember->find($business_member_id);
        if (!$employee) return api_response($request, null, 404);
        $member = $this->repo->find($business_member['member_id']);
        $this->setModifier($member);

        $this->profileRequester
            ->setBusinessMember($employee)
            ->setMobile($request->mobile)
            ->setDateOfBirth($request->dob)
            ->setAddress($request->address)
            ->setNationality($request->nationality)
            ->setNidNo($request->nid_no)
            ->setPassportNo($request->passport_no)
            ->setBloodGroup($request->blood_group)
            ->setSocialLinks($request->social_links)
            ->setNidFrontImage($request->nid_front)
            ->setNidBackImage($request->nid_back)
            ->setPassportImage($request->passport_image);

        if ($this->profileRequester->hasError()) return api_response($request, null, $this->profileRequester->getErrorCode(), ['message' => $this->profileRequester->getErrorMessage()]);

        $personal_info_updater->setProfileRequester($this->profileRequester)->update();

        return api_response($request, null, 200);
    }

    private function isFile($file)
    {
        if ($file instanceof Image || $file instanceof UploadedFile) return true;
        return false;
    }

    /**
     * @param $approval_requests
     * @return int
     */
    private function countPendingApprovalRequests($approval_requests)
    {
        $pending_leave_count = 0;
        foreach ($approval_requests as $approval_request) {
            $requestable = $approval_request->requestable;
            if ($requestable->status === 'pending') {
                $pending_leave_count++;
            }
        }
        return $pending_leave_count;
    }

    /**
     * @param $last_attendance
     * @param $last_attendance_log
     * @param ActionProcessor $action_processor
     * @return array
     */
    private function checkNoteRequired($last_attendance, $last_attendance_log, ActionProcessor $action_processor)
    {
        $is_note_required = 0;
        $note_action = null;

        $checkin = $action_processor->setActionName(Actions::CHECKIN)->getAction();
        $checkout = $action_processor->setActionName(Actions::CHECKOUT)->getAction();
        if ($last_attendance_log['action'] == Actions::CHECKIN && $checkin->isLateNoteRequiredForSpecificDate($last_attendance['date'], $last_attendance['checkin_time'])) {
            $is_note_required = 1;
            $note_action = Actions::CHECKIN;
        }
        if ($last_attendance_log['action'] == Actions::CHECKOUT && $checkout->isLeftEarlyNoteRequiredForSpecificDate($last_attendance['date'], $last_attendance['checkout_time'])) {
            $is_note_required = 1;
            $note_action = Actions::CHECKOUT;
        }

        return [
            'is_note_required' => $is_note_required,
            'note_action' => $note_action
        ];
    }
}
