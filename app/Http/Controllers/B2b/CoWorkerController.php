<?php namespace App\Http\Controllers\B2b;

use App\Jobs\Business\SendEmailForPublishTenderToBusiness;
use App\Models\Business;
use App\Models\Department;
use App\Models\Procurement;
use App\Transformers\Business\CoWorkerReportDetailsTransformer;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Intervention\Image\Image;
use Sheba\Business\CoWorker\Designations;
use Sheba\Business\CoWorker\Filter\CoWorkerInfoFilter;
use Sheba\Business\CoWorker\Requests\Requester as CoWorkerRequester;
use Sheba\Business\CoWorker\Excel as EmployeeExcel;
use App\Transformers\Business\CoWorkerDetailTransformer;
use Sheba\Business\CoWorker\Creator as CoWorkerCreator;
use Sheba\Business\CoWorker\Sorting\CoWorkerInfoSort;
use Sheba\Business\CoWorker\Updater as CoWorkerUpdater;
use App\Transformers\Business\CoWorkerListTransformer;
use Sheba\Business\CoWorker\Requests\EmergencyRequest;
use Sheba\Business\CoWorker\Requests\FinancialRequest;
use Sheba\Business\CoWorker\Requests\OfficialRequest;
use Sheba\Business\CoWorker\Requests\PersonalRequest;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use App\Sheba\Business\Salary\Requester as CoWorkerSalaryRequester;
use Sheba\Business\CoWorker\Validation\CoWorkerExistenceCheck;
use Sheba\Dal\Salary\SalaryRepository;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\ProfileRepository;
use League\Fractal\Resource\Collection;
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
use App\Models\Member;
use Carbon\Carbon;
use Sheba\Business\CoWorker\SalaryCertificate\SalaryCertificateInfo;
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
    /** @var CoWorkerSalaryRequester */
    private $coWorkerSalaryRequester;
    /** @var SalaryRepository */
    private $salaryRepository;
    /**  @var CoWorkerInfoFilter $coWorkerInfoFilter */
    private $coWorkerInfoFilter;
    /** @var CoWorkerInfoSort $coWorkerInfoSort */
    private $coWorkerInfoSort;
    /** @var BusinessMemberRepositoryInterface $businessMemberRepository */
    private $businessMemberRepository;
    /** @var CoWorkerExistenceCheck $coWorkerExistenceCheck */
    private $coWorkerExistenceCheck;


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
     * @param CoWorkerSalaryRequester $co_worker_salary_requester
     * @param SalaryRepository $salary_repository
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param CoWorkerInfoFilter $co_worker_info_filter
     * @param CoWorkerInfoSort $co_worker_info_sort
     */
    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository, BasicRequest $basic_request,
                                EmergencyRequest $emergency_request, FinancialRequest $financial_request,
                                OfficialRequest $official_request, PersonalRequest $personal_request,
                                CoWorkerCreator $co_worker_creator, CoWorkerUpdater $co_worker_updater,
                                CoWorkerRequester $coWorker_requester, CoWorkerSalaryRequester $co_worker_salary_requester,
                                SalaryRepository $salary_repository, BusinessMemberRepositoryInterface $business_member_repo,
                                CoWorkerInfoFilter $co_worker_info_filter, CoWorkerInfoSort $co_worker_info_sort,
                                CoWorkerExistenceCheck $co_worker_existence_check)
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
        $this->coWorkerSalaryRequester = $co_worker_salary_requester;
        $this->salaryRepository = $salary_repository;
        $this->coWorkerInfoFilter = $co_worker_info_filter;
        $this->coWorkerInfoSort = $co_worker_info_sort;
        $this->businessMemberRepository = $business_member_repo;
        $this->coWorkerExistenceCheck = $co_worker_existence_check;
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
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

        $this->coWorkerCreator->setBusiness($business)
            ->setEmail($request->email)
            ->setStatus(Statuses::ACTIVE)
            ->setBasicRequest($basic_request)
            ->setManagerMember($manager_member);

        if ($this->coWorkerCreator->hasError()) {
            return api_response($request, null, $this->coWorkerCreator->getErrorCode(), ['message' => $this->coWorkerCreator->getErrorMessage()]);
        }
        $member = $this->coWorkerCreator->basicInfoStore();

        if ($member) return api_response($request, null, 200, ['member_id' => $member->id, 'pro_pic' => $member->profile->pro_pic]);
        return api_response($request, null, 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoles(Request $request)
    {
        $business_department_ids = BusinessDepartment::query()->where('business_id', $request->business->id)->pluck('id')->toArray();
        $roles = BusinessRole::query()->whereIn('business_department_id', $business_department_ids)->pluck('name')->toArray();
        $designations_list = Designations::getDesignations();
        $all_roles = collect(array_merge($roles, $designations_list))->unique();
        if ($request->has('search')) {
            $all_roles = array_filter($all_roles->toArray(), function ($role) use ($request) {
                return str_contains(strtoupper($role), strtoupper($request->search));
            });
        }
        return api_response($request, $all_roles, 200, ['roles' => collect($all_roles)->values()]);
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function basicInfoEdit($business, $member_id, Request $request)
    {
        $validation_data = [
            'first_name' => 'required|string',
            'last_name' => 'sometimes|required|string',
            'email' => 'required|email',
            'department' => 'required|integer',
            'role' => 'required|string',
            'manager_employee' => 'sometimes|required'
        ];
        $validation_data['pro_pic'] = $this->isFile($request->pro_pic) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';
        $this->validate($request, $validation_data);

        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $basic_request = $this->basicRequest->setProPic($request->pro_pic)
            ->setFirstName($request->first_name)
            ->setLastName($request->last_name)
            ->setEmail($request->email)
            ->setDepartment($request->department)
            ->setRole($request->role)
            ->setManagerEmployee($request->manager_employee);

        $this->coWorkerUpdater->setBasicRequest($basic_request)->setBusiness($business)->setMember($member_id)->setEmail($request->email);

        if ($this->coWorkerUpdater->hasError()) {
            return api_response($request, null, $this->coWorkerUpdater->getErrorCode(), ['message' => $this->coWorkerUpdater->getErrorMessage()]);
        }
        list($business_member, $profile_pic_name, $profile_pic) = $this->coWorkerUpdater->basicInfoUpdate();

        if ($business_member)
            return api_response($request, null, 200, ['profile_pic_name' => $profile_pic_name, 'profile_pic' => $profile_pic]);

        return api_response($request, null, 404);
    }

    /**
     * @param $file
     * @return bool
     */
    private function isFile($file)
    {
        if ($file instanceof Image || $file instanceof UploadedFile) return true;
        return false;
    }

    /**
     * @param $data
     * @return bool
     */
    private function isNull($data)
    {
        if ($data == 'null') return true;
        if ($data == null) return true;
        return false;
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function officialInfoEdit($business, $member_id, Request $request)
    {
        $validation_data = ['employee_id' => 'sometimes|required|string', 'grade' => 'sometimes|required', 'previous_institution' => 'sometimes|required'];
        if (!$this->isNull($request->employee_type)) $validation_data += ['employee_type' => 'sometimes|required|in:permanent,on_probation,contractual,intern'];
        if (!$this->isNull($request->join_date)) $validation_data += ['join_date' => 'sometimes|required|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d')];
        $this->validate($request, $validation_data);

        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        $business = $request->business;

        $official_request = $this->officialRequest->setEmployeeId($request->employee_id)
            ->setJoinDate($request->join_date)
            ->setGrade($request->grade)
            ->setEmployeeType($request->employee_type)
            ->setPreviousInstitution($request->previous_institution);
        $business_member = $this->coWorkerUpdater->setOfficialRequest($official_request)->setBusiness($business)->setMember($member_id)->officialInfoUpdate();

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
        $validation_data = [
            'mobile' => 'sometimes|required',
            'date_of_birth' => 'sometimes|required',
            'address' => 'sometimes|required',
            'nationality' => 'sometimes|required',
            'nid_number' => 'sometimes|required'
        ];

        $validation_data['nid_front'] = $this->isFile($request->nid_front) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';
        $validation_data['nid_back'] = $this->isFile($request->nid_back) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';
        $this->validate($request, $validation_data);

        $member = $request->manager_member;
        $business = $request->business;
        $this->setModifier($member);
        if ($request->has('mobile') && $request->mobile != 'null') $request->mobile = formatMobile($request->mobile);

        $personal_request = $this->personalRequest
            ->setPhone($request->mobile)
            ->setDateOfBirth($request->date_of_birth)
            ->setAddress($request->address)
            ->setNationality($request->nationality)
            ->setNidNumber($request->nid_number)
            ->setNidFront($request->nid_front)
            ->setNidBack($request->nid_back);

        $this->coWorkerUpdater->setPersonalRequest($personal_request)->setBusiness($business)->setMember($member_id)->setMobile($request->mobile);
        if ($this->coWorkerUpdater->hasError())
            return api_response($request, null, $this->coWorkerUpdater->getErrorCode(), ['message' => $this->coWorkerUpdater->getErrorMessage()]);

        list($profile, $nid_image_front_name, $nid_image_front, $nid_image_back_name, $nid_image_back) = $this->coWorkerUpdater->personalInfoUpdate();

        if ($profile)
            return api_response($request, NULL, 200, ['nid_image_front_name' => $nid_image_front_name, 'nid_image_front' => $nid_image_front, 'nid_image_back_name' => $nid_image_back_name, 'nid_image_back' => $nid_image_back]);

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
        $validation_data = ['tin_number ' => 'sometimes|required', 'bank_name ' => 'sometimes|required', 'bank_account_number ' => 'sometimes|required'];
        $validation_data ['tin_certificate '] = $this->isFile($request->tin_certificate) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';
        $this->validate($request, $validation_data);

        $manager_member = $request->manager_member;
        $business = $request->business;
        $this->setModifier($manager_member);

        $financial_request = $this->financialRequest->setTinNumber($request->tin_number)
            ->setTinCertificate($request->tin_certificate)
            ->setBankName($request->bank_name)
            ->setBankAccNumber($request->bank_account_number);

        list($profile, $image_name, $image_link) = $this->coWorkerUpdater
            ->setFinancialRequest($financial_request)
            ->setBusiness($business)
            ->setMember($member_id)
            ->financialInfoUpdate();

        if ($profile) return api_response($request, 1, 200, [
            'tin_certificate_name' => $image_name,
            'tin_certificate_link' => $image_link
        ]);
        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function salaryInfoEdit($business, $member_id, Request $request)
    {
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        $business = $request->business;
        $this->coWorkerSalaryRequester->setMember($member_id)
            ->setGrossSalary($request->gross_salary)
            ->setBreakdownPercentage($request->breakdown_percentage)
            ->setManagerMember($manager_member)
            ->createOrUpdate();
        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function emergencyInfoEdit($business, $member_id, Request $request)
    {
        $validation_data = [
            'name ' => 'sometimes|required',
            'mobile' => 'string',
            'relationship ' => 'sometimes|required'
        ];
        $validation_data['mobile'] = $this->isNull($request->mobile) ? 'string' : 'sometimes|string|mobile:bd';
        $this->validate($request, $validation_data);

        $manager_member = $request->manager_member;
        $business = $request->business;
        $this->setModifier($manager_member);

        $emergency_request = $this->emergencyRequest->setEmergencyContractPersonName($request->name)
            ->setEmergencyContractPersonMobile($request->mobile)
            ->setRelationshipEmergencyContractPerson($request->relationship);

        $member = $this->coWorkerUpdater->setEmergencyRequest($emergency_request)->setBusiness($business)->setMember($member_id)->emergencyInfoUpdate();
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
        /** @var Business $business */
        $business = $request->business;
        $business_members = $business->getAllBusinessMember();
        list($offset, $limit) = calculatePagination($request);

        if ($request->has('for') && $request->for == 'prorate') {
            $department_info = $this->getEmployeeGroupByDepartment($business);
            return api_response($request, $department_info, 200, ['department_info' => $department_info]);
        }

        if ($request->has('department')) $business_members = $this->coWorkerInfoFilter->filterByDepartment($business_members, $request);
        if ($request->has('status')) $business_members = $this->coWorkerInfoFilter->filterByStatus($business_members, $request);

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $employees = new Collection($business_members->get(), new CoWorkerListTransformer());
        $employees = collect($manager->createData($employees)->toArray()['data']);

        $employees = $this->coWorkerInfoSort->sortCoworkerInList($employees, $request);
        $employees = $this->coWorkerInfoFilter->filterCoworkerInList($employees, $request);

        $total_employees = count($employees);
        $limit = $this->getLimit($request, $limit, $total_employees);
        $employees = collect($employees)->splice($offset, $limit);

        if (count($employees) > 0) return api_response($request, $employees, 200, [
            'employees' => $employees,
            'total_employees' => $total_employees
        ]);
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
        if (!is_numeric($member_id)) return api_response($request, null, 400);
        $member = Member::findOrFail($member_id);
        if (!$member) return api_response($request, null, 404);
        $business = $request->business;
        $is_inactive_filter_applied = false;

        if (!$member->businessMember) {
            $is_inactive_filter_applied = true;
            $business_member = $this->businessMemberRepository->builder()
                ->where('business_id', $business->id)
                ->where('member_id', $member->id)
                ->where('status', Statuses::INACTIVE)
                ->first();
            if (!$business_member) return api_response($request, null, 404);
            $member->setRelation('businessMemberGenerated', $business_member->load([
                'role' => function ($q) {
                    $q->select('business_roles.id', 'business_department_id', 'name')->with([
                        'businessDepartment' => function ($q) {
                            $q->select('business_departments.id', 'business_id', 'name');
                        }
                    ]);
                }
            ]));
            $member->push();
        }

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $member = new Item($member, new CoWorkerDetailTransformer($business, $is_inactive_filter_applied));
        $employee = $manager->createData($member)->toArray()['data'];

        if (count($employee) > 0) return api_response($request, $employee, 200, ['employee' => $employee]);
        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $business_member_id
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function statusUpdate($business, $business_member_id, Request $request)
    {
        $this->validate($request, ['status' => 'required|string|in:' . implode(',', Statuses::get())]);
        $requester_business_member = $request->business_member;
        if ($requester_business_member == $business_member_id) return api_response($request, null, 404, ['message' => 'Sorry, You cannot deactivated yourself as super admin.']);

        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $business_member = $this->businessMemberRepository->find($business_member_id);
        $coWorker_requester = $this->coWorkerRequester->setStatus($request->status);
        $this->coWorkerUpdater->setCoWorkerRequest($coWorker_requester)->setBusiness($business)->setBusinessMember($business_member);

        if ($this->isReInviteFeasible($business_member->status, $request->status)) {
            $this->coWorkerUpdater->reInvite();
            return api_response($request, 1, 200);
        }
        if ($this->isDeleteFeasible($business_member->status, $request->status)) {
            $this->coWorkerUpdater->delete();
            return api_response($request, 1, 200);
        }
        if ($this->isActive($request->status)) {
            $this->coWorkerExistenceCheck->setBusiness($business)->setBusinessMember($business_member);
            if ($this->coWorkerExistenceCheck->hasError()) {
                return api_response($request, null, $this->coWorkerExistenceCheck->getErrorCode(), ['message' => $this->coWorkerExistenceCheck->getErrorMessage()]);
            }
        }
        $business_member = $this->coWorkerUpdater->statusUpdate();
        if ($business_member) return api_response($request, 1, 200);

        return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function bulkStatusUpdate($business, Request $request)
    {
        $this->validate($request, [
            'employee_ids' => "required",
            'status' => 'required|string|in:' . implode(',', Statuses::get())
        ]);
        $business = $request->business;
        $manager_member = $request->manager_member;
        $requester_business_member = $request->business_member;
        $this->setModifier($manager_member);
        $business_member_ids = json_decode($request->employee_ids);

        if (in_array($requester_business_member->id, $business_member_ids)) return api_response($request, null, 404, ['message' => 'One of the Ids contains superadmin ID, which cannot be deactivated, Please check again.']);

        foreach ($business_member_ids as $business_member_id) {
            $business_member = BusinessMember::where('id', $business_member_id)->first();
            $coWorker_requester = $this->coWorkerRequester->setStatus($request->status);
            $this->coWorkerUpdater->setCoWorkerRequest($coWorker_requester)->setBusiness($business)->setBusinessMember($business_member);
            if ($this->isReInviteFeasible($business_member->status, $request->status)) $this->coWorkerUpdater->reInvite();
            if ($this->isDeleteFeasible($business_member->status, $request->status)) $this->coWorkerUpdater->delete();
            if ($business_member->status == $request->status) continue;
            if ($request->status == Statuses::DELETE) continue;
            $this->coWorkerUpdater->statusUpdate();
        }
        return api_response($request, null, 200);
    }

    /**
     * @param $business_member_current_status
     * @param $requested_status
     * @return bool
     */
    private function isReInviteFeasible($business_member_current_status, $requested_status)
    {
        if ($business_member_current_status == Statuses::INVITED && $requested_status == Statuses::INVITED)
            return true;
    }

    /**
     * @param $business_member_current_status
     * @param $requested_status
     * @return bool
     */
    private function isDeleteFeasible($business_member_current_status, $requested_status)
    {
        if ($business_member_current_status == Statuses::INVITED && $requested_status == Statuses::DELETE)
            return true;
    }

    /**
     * @param $requested_status
     * @return bool
     */
    private function isActive($requested_status)
    {
        if ($requested_status == Statuses::ACTIVE) return true;
        return false;
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
        $this->validate($request, ['name' => 'required|string', 'department_id' => 'required|integer']);
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
     * @param ExcelHandler $excel_handler
     * @return JsonResponse
     * @throws NotAssociativeArray
     * @throws Exception
     */
    public function sendInvitation($business, Request $request, ExcelHandler $excel_handler)
    {
        $this->validate($request, ['emails' => "required"]);

        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);
        $errors = [];

        $emails = json_decode($request->emails);
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, ['email' => $email, 'message' => 'Invalid email address']);
                continue;
            }
            $this->basicRequest->setEmail($email);
            $this->coWorkerCreator->setBusiness($business)->setEmail($email)->setStatus(Statuses::INVITED)->setBasicRequest($this->basicRequest);

            if ($this->coWorkerCreator->hasError()) {
                array_push($errors, ['email' => $email, 'message' => $this->coWorkerCreator->getErrorMessage()]);
                $this->coWorkerCreator->resetError();
                continue;
            }

            $this->coWorkerCreator->basicInfoStore();
        }

        if ($errors) {
            $file_name = Carbon::now()->timestamp . "_co_worker_invite_error_$business->id.xlsx";
            $file = $excel_handler->setName('Co worker Invite')->setFilename($file_name)->setDownloadFormat('xlsx')->createReport($errors)->save();
            $file_path = $this->saveFileToCDN($file, getCoWorkerInviteErrorFolder(), $file_name);
            unlink($file);

            if ($this->isFailedToCreateAllCoworker($errors, $emails)) {
                return api_response($request, null, 422, [
                    'message' => 'Alert! Invitations failed',
                    'description' => "Invited co-worker/s already exist in the co-worker list. Download the excel file to see details",
                    'link' => $file_path
                ]);
            }

            return api_response($request, null, 303, [
                'message' => 'Alert! Some invitations failed',
                'description' => "Invited co-worker/s already exist in the co-worker list. Download the excel file to see details",
                'link' => $file_path
            ]);
        }

        return api_response($request, null, 200);
    }

    /**
     * @param array $errors
     * @param $emails
     * @return bool
     */
    private function isFailedToCreateAllCoworker(array $errors, $emails)
    {
        return count($errors) == count($emails);
    }

    /**
     * @param $business
     * @return array
     */
    private function getEmployeeGroupByDepartment($business)
    {
        $business_departments = BusinessDepartment::published()->where('business_id', $business->id)
            ->select('id', 'business_id', 'name')->get();
        $department_info = [];
        foreach ($business_departments as $business_department) {
            $members = $business->membersWithProfileAndAccessibleBusinessMember();
            $members = $members->whereHas('businessMember', function ($q) use ($business_department) {
                $q->whereHas('role', function ($q) use ($business_department) {
                    $q->whereHas('businessDepartment', function ($q) use ($business_department) {
                        $q->where('business_departments.id', $business_department->id);
                    });
                });
            });
            $members = $members->get()->unique();
            $employee_data = [];
            foreach ($members as $member) {
                $profile = $member->profile;
                $business_member = $member->businessMember;
                array_push($employee_data, [
                    'id' => $member->id,
                    'employee_id' => $business_member->employee_id,
                    'business_member_id' => $business_member->id,
                    'profile' => [
                        'id' => $profile->id,
                        'name' => $profile->name,
                        'pro_pic' => $profile->pro_pic,
                        'mobile' => $profile->mobile,
                        'email' => $profile->email,
                    ]
                ]);
            }
            array_push($department_info, [
                'department_id' => $business_department->id,
                'department' => $business_department->name,
                'employees' => $employee_data
            ]);
        }
        return $department_info;
    }

    /**
     * @param Request $request
     * @param $limit
     * @param $total_employees
     * @return mixed
     */
    private function getLimit(Request $request, $limit, $total_employees)
    {
        if ($request->has('limit') && $request->limit == 'all') return $total_employees;
        return $limit;
    }

    /**
     * @param $business
     * @param Request $request
     * @param EmployeeExcel $employee_report
     */
    public function downloadEmployeesReport($business, Request $request, EmployeeExcel $employee_report)
    {
        /** @var Business $business */
        $business = $request->business;
        $business_members = $business->getAllBusinessMember();

        if ($request->has('department')) $business_members = $this->coWorkerInfoFilter->filterByDepartment($business_members, $request);
        if ($request->has('status')) $business_members = $this->coWorkerInfoFilter->filterByStatus($business_members, $request);

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $employees = new Collection($business_members->get(), new CoWorkerReportDetailsTransformer());
        $employees = collect($manager->createData($employees)->toArray()['data']);

        $employees = $this->coWorkerInfoSort->sortCoworkerInList($employees, $request);
        $employees = $this->coWorkerInfoFilter->filterCoworkerInList($employees, $request);

        $employees = collect($employees);

        return $employee_report->setEmployee($employees->toArray())->get();
    }

    /**
     * @param $business
     * @param $member_id
     * @param Request $request
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param SalaryCertificateInfo $salaryCertificateInfo
     * @return JsonResponse
     */
    public function salaryCertificatePdf($business, $member_id, Request $request, BusinessMemberRepositoryInterface $business_member_repo, SalaryCertificateInfo $salaryCertificateInfo)
    {
        $is_inactive_filter_applied = false;
        if (!is_numeric($member_id)) return api_response($request, null, 400);
        $member = Member::findOrFail($member_id);
        if (!$member) return api_response($request, null, 404);
        $business = $request->business;

        if (!$member->businessMember) {
            $business_member = $business_member_repo->builder()
                ->where('business_id', $business->id)
                ->where('member_id', $member->id)
                ->where('status', Statuses::INACTIVE)
                ->first();
            if (!$business_member) return api_response($request, null, 404);
            $is_inactive_filter_applied = true;
            $member->setRelation('businessMemberGenerated', $business_member->load([
                'role' => function ($q) {
                    $q->select('business_roles.id', 'business_department_id', 'name')->with([
                        'businessDepartment' => function ($q) {
                            $q->select('business_departments.id', 'business_id', 'name');
                        }
                    ]);
                }
            ]));
            $member->push();
        }

        $business_member = $is_inactive_filter_applied ? $member->businessMemberGenerated : $member->businessMember;

        $salary_certificate_info = $salaryCertificateInfo->setBusiness($business)
            ->setMember($member)
            ->setBusinessMember($business_member)
            ->get();

        if ($request->file == 'pdf') {
            return App::make('dompdf.wrapper')->loadView('pdfs.payroll.salary_certificate', compact('salary_certificate_info'))->download("salary_certificate.pdf");
        }

        return api_response($request, null, 200, ['salary_info_details' => $salary_certificate_info]);
    }
}
