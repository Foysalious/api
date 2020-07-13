<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\CoWorker\Requests\Requester as CoWorkerRequester;
use App\Transformers\Business\CoWorkerDetailTransformer;
use Sheba\Business\CoWorker\Creator as CoWorkerCreator;
use Sheba\Business\CoWorker\Updater as CoWorkerUpdater;
use Sheba\Business\CoWorker\Requests\EmergencyRequest;
use Sheba\Business\CoWorker\Requests\FinancialRequest;
use Sheba\Business\CoWorker\Requests\OfficialRequest;
use Sheba\Business\CoWorker\Requests\PersonalRequest;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use Sheba\Repositories\ProfileRepository;
use App\Jobs\SendBusinessRequestEmail;
use Sheba\FileManagers\CdnFileManager;
use App\Transformers\CustomSerializer;
use Sheba\Business\CoWorker\Statuses;
use App\Repositories\FileRepository;
use App\Http\Controllers\Controller;
use Sheba\FileManagers\FileManager;
use App\Models\BusinessDepartment;
use League\Fractal\Resource\Item;
use Illuminate\Http\JsonResponse;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use App\Models\BusinessRole;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use App\Models\Profile;
use App\Models\Member;
use Carbon\Carbon;
use DB;

class CoWorkerController extends Controller
{
    use CdnFileManager, FileManager, ModificationFields;

    /** @var FileRepository $fileRepository */
    private $fileRepository;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var BasicRequest $basicRequest */
    private $basicRequest;
    /** @var EmergencyRequest $emergencyRequest */
    private $emergencyRequest;
    /** @var FinancialRequest $financialRequest */
    private $financialRequest;
    /** @var OfficialRequest $officialRequest */
    private $officialRequest;
    /** @var PersonalRequest $personalRequest */
    private $personalRequest;
    /** @var CoWorkerCreator $coWorkerCreator */
    private $coWorkerCreator;
    /** @var CoWorkerUpdater $coWorkerUpdater */
    private $coWorkerUpdater;
    /** @var CoWorkerRequester $coWorkerRequester */
    private $coWorkerRequester;

    /**
     * CoWorkerController constructor.
     * @param FileRepository $file_repository
     * @param ProfileRepository $profile_repository
     * @param BasicRequest $basic_request
     * @param EmergencyRequest $emergency_request
     * @param FinancialRequest $financial_request
     * @param OfficialRequest $official_request
     * @param PersonalRequest $personal_request
     * @param CoWorkerCreator $co_worker_creator
     * @param CoWorkerUpdater $co_worker_updater
     * @param CoWorkerRequester $coWorker_requester
     */
    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository, BasicRequest $basic_request,
                                EmergencyRequest $emergency_request, FinancialRequest $financial_request,
                                OfficialRequest $official_request, PersonalRequest $personal_request,
                                CoWorkerCreator $co_worker_creator, CoWorkerUpdater $co_worker_updater,
                                CoWorkerRequester $coWorker_requester)
    {
        $this->fileRepository = $file_repository;
        $this->profileRepository = $profile_repository;
        $this->basicRequest = $basic_request;
        $this->emergencyRequest = $emergency_request;
        $this->financialRequest = $financial_request;
        $this->officialRequest = $official_request;
        $this->personalRequest = $personal_request;
        $this->coWorkerCreator = $co_worker_creator;
        $this->coWorkerUpdater = $co_worker_updater;
        $this->coWorkerRequester = $coWorker_requester;
    }

    public function basicInfoStore($business, Request $request)
    {
        $this->validate($request, [
            'pro_pic' => 'sometimes|required|mimes:jpg,jpeg,png,pdf',
            'first_name' => 'required|string',
            'last_name' => 'sometimes|required|string',
            'email' => 'required|email',
            'department' => 'required|integer',
            'role' => 'required|integer',
            'manager_employee' => 'sometimes|required|integer',
        ]);
        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);

        $basic_request = $this->basicRequest->setProPic($request->pro_pic)
            ->setFirstName($request->first_name)
            ->setLastName($request->last_name)
            ->setEmail($request->email)
            ->setDepartment($request->department)
            ->setRole($request->role)
            ->setManagerEmployee($request->manager_employee);
        $this->coWorkerCreator->setBasicRequest($basic_request)->storeBasicInfo();
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function basicInfoEdit($business, $member_id, Request $request)
    {
        $this->validate($request, [
            'pro_pic' => 'sometimes|required|mimes:jpg,jpeg,png,pdf',
            'first_name' => 'required|string',
            'last_name' => 'sometimes|required|string',
            'email' => 'required|email',
            'department' => 'required|integer',
            'role' => 'required|string',
            'manager_employee' => 'sometimes|required|integer'
        ]);
        $member = $request->manager_member;
        $this->setModifier($member);
        $basic_request = $this->basicRequest->setProPic($request->file('pro_pic'))
            ->setFirstName($request->first_name)
            ->setLastName($request->last_name)
            ->setEmail($request->email)
            ->setDepartment($request->department)
            ->setRole($request->role)
            ->setManagerEmployee($request->manager_employee);
        list($business_member, $profile_pic_name, $profile_pic) = $this->coWorkerUpdater->setBasicRequest($basic_request)->setMember($member_id)->basicInfoUpdate();
        if ($business_member) return api_response($request, 1, 200, ['profile_pic_name' => $profile_pic_name, 'profile_pic' => $profile_pic]);
        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function officialInfoEdit($business, $member_id, Request $request)
    {
        $this->validate($request, [
            'join_date' => 'sometimes|required|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
            'grade' => 'sometimes|required|string',
            'employee_type' => 'sometimes|required|in:permanent,on_probation,contractual,intern',
            'previous_institution' => 'sometimes|required|string',
        ]);
        $member = $request->manager_member;
        $this->setModifier($member);

        $official_request = $this->officialRequest->setJoinDate($request->join_date)
            ->setGrade($request->grade)
            ->setEmployeeType($request->employee_type)
            ->setPreviousInstitution($request->previous_institution);
        $business_member = $this->coWorkerUpdater->setOfficialRequest($official_request)->setMember($member_id)->officialInfoUpdate();
        if ($business_member) return api_response($request, 1, 200);
        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function personalInfoEdit($business, $member_id, Request $request)
    {
        $this->validate($request, [
            'mobile' => 'string|mobile:bd',
            'date_of_birth ' => 'sometimes|required|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
            'address ' => 'sometimes|required|string',
            'nationality ' => 'sometimes|required|string',
            'nid_number ' => 'sometimes|required|integer',
            'nid_front ' => 'sometimes|required|mimes:jpg,jpeg,png,pdf',
            'nid_back ' => 'sometimes|required|mimes:jpg,jpeg,png,pdf',
        ]);
        $member = $request->manager_member;
        $this->setModifier($member);
        $personal_request = $this->personalRequest->setPhone($request->mobile)
            ->setDateOfBirth($request->date_of_birth)
            ->setAddress($request->address)
            ->setNationality($request->nationality)
            ->setNidNumber($request->nid_number)
            ->setNidFront($request->file('nid_front'))->setNidBack($request->file('nid_back'));
        list($profile, $nid_image_front_name, $nid_image_front, $nid_image_back_name, $nid_image_back) = $this->coWorkerUpdater->setPersonalRequest($personal_request)->setMember($member_id)->personalInfoUpdate();
        if ($profile) {
            return api_response($request, 1, 200, [
                'nid_image_front_name' => $nid_image_front_name,
                'nid_image_front' => $nid_image_front,
                'nid_image_back_name' => $nid_image_back_name,
                'nid_image_back' => $nid_image_back
            ]);
        }
        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function financialInfoEdit($business, $member_id, Request $request)
    {
        $this->validate($request, [
            'tin_number ' => 'sometimes|required|string',
            'tin_certificate ' => 'sometimes|required|mimes:jpg,jpeg,png,pdf',
            'bank_name ' => 'sometimes|required|string',
            'bank_account_number ' => 'sometimes|required|string'
        ]);

        $member = $request->manager_member;
        $this->setModifier($member);
        $financial_request = $this->financialRequest->setTinNumber($request->tin_number)
            ->setTinCertificate($request->file('tin_certificate'))
            ->setBankName($request->bank_name)
            ->setBankAccNumber($request->bank_account_number);
        list($profile, $image_name, $image_link) = $this->coWorkerUpdater->setFinancialRequest($financial_request)->setMember($member_id)->financialInfoUpdate();
        if ($profile) return api_response($request, 1, 200, ['tin_certificate_name' => $image_name, 'tin_certificate_link' => $image_link]);
        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function emergencyInfoEdit($business, $member_id, Request $request)
    {
        $this->validate($request, [
            'name ' => 'sometimes|required|string',
            'mobile' => 'string|mobile:bd',
            'relationship ' => 'sometimes|required|string'
        ]);
        $member = $request->manager_member;
        $this->setModifier($member);
        $emergency_request = $this->emergencyRequest->setEmergencyContractPersonName($request->name)
            ->setEmergencyContractPersonMobile($request->mobile)
            ->setRelationshipEmergencyContractPerson($request->relationship);
        $member = $this->coWorkerUpdater->setEmergencyRequest($emergency_request)->setMember($member_id)->emergencyInfoUpdate();
        if ($member) return api_response($request, 1, 200);
        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function statusUpdate($business, $member_id, Request $request)
    {
        $this->validate($request, [
            'status' => 'required|in:active,inactive,invited',
        ]);
        $member = $request->manager_member;
        $this->setModifier($member);
        $coWorker_requester = $this->coWorkerRequester->setStatus($request->status);
        $business_member = $this->coWorkerUpdater->setCoWorkerRequest($coWorker_requester)->setMember($member_id)->statusUpdate();
        if ($business_member) return api_response($request, 1, 200);
        return api_response($request, null, 404);
    }
    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function store($business, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'mobile' => 'required|string|mobile:bd',
            'email' => 'required|email',
            'role' => 'required|integer',
            'manager_employee_id' => 'integer'
        ]);

        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);

        $manager_business_member = null;
        $email_profile = $this->profileRepository->where('email', $request->email)->first();
        $mobile_profile = $this->profileRepository->where('mobile', formatMobile($request->mobile))->first();

        if ($email_profile) $profile = $email_profile;
        elseif ($mobile_profile) $profile = $mobile_profile;
        else $profile = null;

        $co_member = collect();
        if ($request->has('manager_employee_id'))
            $manager_business_member = BusinessMember::where([
                ['member_id', $request->manager_employee_id],
                ['business_id', $business->id]
            ])->first();

        if (!$profile) {
            $profile = $this->createProfile($member, $request);
            $new_member = $this->makeMember($profile);
            $co_member->push($new_member);

            $business = $member->businesses->first();
            $member_business_data = [
                'business_id' => $business->id,
                'member_id' => $co_member->first()->id,
                'join_date' => Carbon::now(),
                'manager_id' => $manager_business_member ? $manager_business_member->id : null,
                'business_role_id' => $request->role
            ];

            BusinessMember::create($this->withCreateModificationField($member_business_data));
        } else {
            $old_member = $profile->member;
            if ($old_member) {
                if ($old_member->businesses()->where('businesses.id', $business->id)->count() > 0) {
                    return api_response($request, $profile, 200, ['co_worker' => $old_member->id, ['message' => "This person is already added."]]);
                }
                if ($old_member->businesses()->where('businesses.id', '<>', $business->id)->count() > 0) {
                    return api_response($request, null, 403, ['message' => "This person is already connected with another business."]);
                }
                $co_member->push($old_member);
            } else {
                $new_member = $this->makeMember($profile);
                $co_member->push($new_member);
            }
            $this->sendExistingUserMail($profile);
            $member_business_data = [
                'business_id' => $business->id,
                'member_id' => $co_member->first()->id,
                'join_date' => Carbon::now(),
                'manager_id' => $manager_business_member ? $manager_business_member->id : null,
                'business_role_id' => $request->role
            ];

            BusinessMember::create($this->withCreateModificationField($member_business_data));
        }

        return api_response($request, $profile, 200, ['co_worker' => $co_member->first()->id]);
    }

    /**
     * @TODO NEED TO REMOVE THIS. CREATE FROM PROFILE REPO
     *
     * @param $member
     * @param Request $request
     * @return Profile
     */
    private function createProfile($member, Request $request)
    {
        $this->setModifier($member);
        $password = str_random(6);
        $profile_data = [
            'remember_token' => str_random(255),
            'mobile' => !empty($request->mobile) ? formatMobile($request->mobile) : null,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($password)
        ];
        $profile = Profile::create($this->withCreateModificationField($profile_data));
        dispatch((new SendBusinessRequestEmail($request->email))->setPassword($password)->setTemplate('emails.co-worker-invitation'));

        return $profile;
    }

    private function makeMember($profile)
    {
        $this->setModifier($profile);
        $member = new Member();
        $member->profile_id = $profile->id;
        $member->remember_token = str_random(255);
        $member->save();

        return $member;
    }

    private function sendExistingUserMail($profile)
    {
        $CMail = new SendBusinessRequestEmail($profile->email);
        if (empty($profile->password)) {
            $profile->password = str_random(6);
            $CMail->setPassword($profile->password);
            $profile->save();
        }
        $CMail->setTemplate('emails.co-worker-invitation');
        dispatch($CMail);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function index($business, Request $request)
    {
        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);

        $members = $business->members()->select('members.id', 'profile_id')->with([
            'profile' => function ($q) {
                $q->select('profiles.id', 'name', 'pro_pic', 'mobile', 'email');
            },
            'businessMember' => function ($q) {
                $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id', 'status')->with([
                    'role' => function ($q) {
                        $q->select('business_roles.id', 'business_department_id', 'name')->with([
                            'businessDepartment' => function ($q) {
                                $q->select('business_departments.id', 'business_id', 'name');
                            }
                        ]);
                    }
                ]);
            }
        ]);

        if ($request->has('department')) {
            $members->where(function ($query) use ($request) {
                $query->whereHas('businessMember.role.businessDepartment', function ($query) use ($request) {
                    $query->where('name', $request->department);
                });
            });
        }

        $members = $members->get()->unique();
        $employees = [];
        foreach ($members as $member) {
            $profile = $member->profile;
            $role = $member->businessMember->role;

            $employee = [
                'id' => $member->id,
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic,
                'mobile' => $profile->mobile,
                'email' => $profile->email,
                'status' => $member->businessMember->status,
                'department_id' => $role ? $role->businessDepartment->id : null,
                'department' => $role ? $role->businessDepartment->name : null,
                'designation' => $role ? $role->name : null
            ];
            array_push($employees, $employee);
        }

        if (count($employees) > 0)
            return api_response($request, $employees, 200, ['employees' => $employees]);

        return api_response($request, null, 404);
    }

    public function show($business, $employee, Request $request)
    {
        $business_member = BusinessMember::where([['business_id', $business], ['member_id', $employee]])->first();
        $member = $business_member->member;
        if (!$member) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $member = new Item($member, new CoWorkerDetailTransformer());
        $employee = $manager->createData($member)->toArray()['data'];
        if (count($employee) > 0) return api_response($request, $employee, 200, ['employee' => $employee]);
        return api_response($request, null, 404);
    }

    public function update($business, $employee, Request $request)
    {
        $this->validate($request, [
            'manager_employee_id' => 'required|integer',
        ]);
        if ($employee == $request->manager_employee_id) return api_response($request, null, 420, ['message' => 'You cannot be your own manager']);
        $business_member = BusinessMember::where([['business_id', $business], ['member_id', $employee]])->first();
        $manager_business_member = BusinessMember::where([['business_id', $business], ['member_id', $request->manager_employee_id]])->first();
        if ((int)$business != $manager_business_member->business_id || (int)$business != $business_member->business_id) return api_response($request, null, 404);
        $this->setModifier($request->manager_member);
        $business_member->update($this->withUpdateModificationField(['manager_id' => $manager_business_member->id]));
        return api_response($request, null, 200);
    }

    public function departmentRole($business, Request $request)
    {
        $business = $request->business;
        $business_departments = BusinessDepartment::published()->with([
            'businessRoles' => function ($q) {
                $q->select('id', 'name', 'business_department_id');
            }
        ])->where('business_id', $business->id)->select('id', 'business_id', 'name')->get();
        $departments = [];
        foreach ($business_departments as $business_dept) {
            $dept_role = collect();
            foreach ($business_dept->businessRoles as $role) {
                $role = ['id' => $role->id, 'name' => $role->name,];
                $dept_role->push($role);
            }

            $department = [
                'id' => $business_dept->id,
                'name' => $business_dept->name,
                'roles' => $dept_role
            ];
            array_push($departments, $department);
        }
        if (count($departments) > 0)
            return api_response($request, $departments, 200, ['departments' => $departments]);

        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function addBusinessDepartment($business, Request $request)
    {
        $this->validate($request, ['name' => 'required|string']);
        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);

        $data = [
            'business_id' => $business->id,
            'name' => $request->name,
            'is_published' => 1
        ];
        BusinessDepartment::create($this->withCreateModificationField($data));

        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function getBusinessDepartments($business, Request $request)
    {
        $business = $request->business;
        $business_departments = BusinessDepartment::published()->where('business_id', $business->id)->select('id', 'business_id', 'name', 'created_at')->orderBy('id', 'DESC')->get();
        $departments = [];
        foreach ($business_departments as $business_department) {
            $department = [
                'id' => $business_department->id, 'name' => $business_department->name, 'created_at' => $business_department->created_at->format('d/m/y')
            ];
            array_push($departments, $department);
        }

        if (count($departments) > 0)
            return api_response($request, $departments, 200, ['departments' => $departments]);

        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function addBusinessRole($business, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string', 'department_id' => 'required|integer',

        ]);
        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);
        $data = [
            'business_department_id' => $request->department_id,
            'name' => trim($request->name),
            'is_published' => 1,
        ];
        BusinessRole::create($this->withCreateModificationField($data));

        return api_response($request, null, 200);
    }

    public function changeStatus($business, Request $request)
    {
        $this->validate($request, [
            'employee_ids' => "required",
            'status' => 'required|string|in:' . implode(',', Statuses::get())
        ]);

        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function sendInvitation($business, Request $request)
    {
        $this->validate($request, ['emails' => "required"]);

        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);

        foreach (json_decode($request->emails) as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
            $this->basicRequest->setEmail($email);
            $this->coWorkerCreator->setBasicRequest($this->basicRequest)->create();
        }

        return api_response($request, null, 200);
    }
}
