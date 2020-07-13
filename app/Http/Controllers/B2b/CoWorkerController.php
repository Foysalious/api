<?php namespace App\Http\Controllers\B2b;


use Sheba\Business\CoWorker\Requests\Requester as CoWorkerRequester;
use App\Transformers\Business\CoWorkerDetailTransformer;
use Sheba\Business\CoWorker\Creator as CoWorkerCreator;
use Sheba\Business\CoWorker\Updater as CoWorkerUpdater;
use App\Transformers\Business\CoWorkerListTransformer;
use Sheba\Business\CoWorker\Requests\EmergencyRequest;
use Sheba\Business\CoWorker\Requests\FinancialRequest;
use Sheba\Business\CoWorker\Requests\OfficialRequest;
use Sheba\Business\CoWorker\Requests\PersonalRequest;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Repositories\ProfileRepository;
use League\Fractal\Resource\Collection;
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
            'role' => 'required|string',
            'manager_employee' => 'sometimes|required|integer'
        ]);
        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        $basic_request = $this->basicRequest->setProPic($request->file('pro_pic'))
            ->setFirstName($request->first_name)
            ->setLastName($request->last_name)
            ->setEmail($request->email)
            ->setDepartment($request->department)
            ->setRole($request->role)
            ->setManagerEmployee($request->manager_employee);
        $member = $this->coWorkerCreator->setBasicRequest($basic_request)
            ->setBusiness($business)
            ->setManagerMember($manager_member)
            ->basicInfoStore();
        if ($member) return api_response($request, 1, 200, ['member_id' => $member->id]);
        return api_response($request, null, 404);

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
     * @param Request $request
     * @return JsonResponse
     */
    public function index($business, Request $request)
    {
        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
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
        if ($request->has('status')) {
            $members->where(function ($query) use ($request) {
                $query->whereHas('businessMember', function ($query) use ($request) {
                    $query->where('status', $request->status);
                });
            });
        }
        $members = $members->get()->unique();
        if ($request->has('search')) $members = $this->searchWithEmployeeName($members, $request);

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $employees = new Collection($members, new CoWorkerListTransformer());
        $employees = collect($manager->createData($employees)->toArray()['data']);

        if ($request->has('sort_by_name')) $employees = $this->sortByName($employees, $request->sort_by_name)->values();
        if ($request->has('sort_by_department')) $employees = $this->sortByDepartment($employees, $request->sort_by_department)->values();
        if ($request->has('sort_by_status')) $employees = $this->sortByStatus($employees, $request->sort_by_status)->values();

        if (count($employees) > 0) return api_response($request, $employees, 200, ['employees' => $employees]);
        return api_response($request, null, 404);
    }


    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($business, $member_id, Request $request)
    {
        $member = Member::findOrFail($member_id);
        if (!$member) return api_response($request, null, 404);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $member = new Item($member, new CoWorkerDetailTransformer());
        $employee = $manager->createData($member)->toArray()['data'];
        if (count($employee) > 0) return api_response($request, $employee, 200, ['employee' => $employee]);
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
            'status' => 'required|string|in:' . implode(',', Statuses::get())
        ]);
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
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
    public function bulkStatusUpdate($business, Request $request)
    {
        $this->validate($request, [
            'employee_ids' => "required",
            'status' => 'required|string|in:' . implode(',', Statuses::get())
        ]);
        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        foreach (json_decode($request->employee_ids) as $member_id) {
            $business_member = BusinessMember::where([
                ['member_id', $member_id], ['business_id', $business->id]
            ])->first();
            if ($business_member->status == $request->status) continue;
            $coWorker_requester = $this->coWorkerRequester->setStatus($request->status);
            $business_member = $this->coWorkerUpdater->setCoWorkerRequest($coWorker_requester)->setMember($business_member->member->id)->statusUpdate();
        }
        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * @param $employees
     * @param string $sort
     * @return mixed
     */
    private function sortByName($employees, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($employees)->$sort_by(function ($employee, $key) {
            return strtoupper($employee['profile']['name']);
        });
    }

    /**
     * @param $employees
     * @param string $sort
     * @return mixed
     */
    private function sortByDepartment($employees, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($employees)->$sort_by(function ($employee, $key) {
            return strtoupper($employee['department']);
        });
    }

    /**
     * @param $employees
     * @param string $sort
     * @return mixed
     */
    private function sortByStatus($employees, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($employees)->$sort_by(function ($employee, $key) {
            return strtoupper($employee['status']);
        });
    }

    /**
     * @param $members
     * @param Request $request
     * @return mixed
     */
    private function searchWithEmployeeName($members, Request $request)
    {
        return $members->filter(function ($member) use ($request) {
            $profile = $member->profile;
            return str_contains(strtoupper($profile->name), strtoupper($request->search));
        });
    }
}
