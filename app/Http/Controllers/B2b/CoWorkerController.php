<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Jobs\SendBusinessRequestEmail;
use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Repositories\ProfileRepository;
use Throwable;

class CoWorkerController extends Controller
{
    use CdnFileManager, FileManager, ModificationFields;

    /** @var FileRepository $fileRepository */
    private $fileRepository;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;

    /**
     * CoWorkerController constructor.
     * @param FileRepository $file_repository
     * @param ProfileRepository $profile_repository
     */
    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository)
    {
        $this->fileRepository = $file_repository;
        $this->profileRepository = $profile_repository;
    }

    public function store($business, Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string', 'mobile' => 'required|string|mobile:bd', 'email' => 'required|email', 'role' => 'required|integer', 'manager_employee_id' => 'integer',
            ]);
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $manager_business_member = null;
            $email_profile = $this->profileRepository->where('email', $request->email)->first();
            $mobile_profile = $this->profileRepository->where('mobile', formatMobile($request->mobile))->first();
            if ($email_profile) $profile = $email_profile; elseif ($mobile_profile) $profile = $mobile_profile;
            else $profile = null;
            $co_member = collect();
            if ($request->has('manager_employee_id')) $manager_business_member = BusinessMember::where([['member_id', $request->manager_employee_id], ['business_id', $business->id]])->first();
            if (!$profile) {
                $profile = $this->createProfile($member, $request);
                $new_member = $this->makeMember($profile);
                $co_member->push($new_member);

                $business = $member->businesses->first();
                $member_business_data = [
                    'business_id' => $business->id, 'member_id' => $co_member->first()->id, 'join_date' => Carbon::now(), 'manager_id' => $manager_business_member ? $manager_business_member->id : null, 'business_role_id' => $request->role,
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));
            } else {
                $old_member = $profile->member;
                if ($old_member) {
                    if ($old_member->businesses()->where('businesses.id', $business->id)->count() > 0) return api_response($request, $profile, 200, ['co_worker' => $old_member->id, ['message' => "This person is already added."]]);
                    if ($old_member->businesses()->where('businesses.id', '<>', $business->id)->count() > 0) return api_response($request, null, 403, ['message' => "This person is already connected with another business."]);
                    $co_member->push($old_member);
                } else {
                    $new_member = $this->makeMember($profile);
                    $co_member->push($new_member);
                }
                $this->sendExistingUserMail($profile);
                $member_business_data = [
                    'business_id' => $business->id, 'member_id' => $co_member->first()->id, 'join_date' => Carbon::now(), 'manager_id' => $manager_business_member ? $manager_business_member->id : null, 'business_role_id' => $request->role,
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));
            }
            return api_response($request, $profile, 200, ['co_worker' => $co_member->first()->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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
            }, 'businessMember' => function ($q) {
                $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id')->with([
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
                'id' => $member->id, 'name' => $profile->name, 'pro_pic' => $profile->pro_pic, 'mobile' => $profile->mobile, 'email' => $profile->email, 'department_id' => $role ? $role->businessDepartment->id : null, 'department' => $role ? $role->businessDepartment->name : null, 'designation' => $role ? $role->name : null
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
        $manager_member_detail = [];
        if ($business_member->manager_id) {
            $manager_business_member = BusinessMember::findOrFail($business_member->manager_id);
            $manager_member = $manager_business_member->member;
            $manager_profile = $manager_member->profile;
            $manager_member_detail = [
                'id' => $manager_member->id,
                'name' => $manager_profile->name,
                'mobile' => $manager_profile->mobile,
                'email' => $manager_profile->email,
                'pro_pic' => $manager_profile->pro_pic,
                'designation' => $manager_member->businessMember->role ? $manager_member->businessMember->role->name : null,
                'department' => $manager_member->businessMember->role && $manager_member->businessMember->role->businessDepartment ? $manager_member->businessMember->role->businessDepartment->name : null,
            ];
        }

        if (!$member) return api_response($request, null, 404);
        $profile = $member->profile;
        $employee = [
            'id' => $member->id,
            'name' => $profile->name,
            'mobile' => $profile->mobile,
            'email' => $profile->email,
            'pro_pic' => $profile->pro_pic,
            'dob' => Carbon::parse($profile->dob)->format('M j, Y'),
            'designation' => $member->businessMember->role ? $member->businessMember->role->name : null,
            'department' => $member->businessMember->role && $member->businessMember->role->businessDepartment ? $member->businessMember->role->businessDepartment->name : null,
            'manager_detail' => $manager_member_detail
        ];

        if (count($employee) > 0)
            return api_response($request, $employee, 200, ['employee' => $employee]);

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
                'id' => $business_dept->id, 'name' => $business_dept->name, 'roles' => $dept_role
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
}
