<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessTrip;
use App\Models\Driver;
use App\Models\Profile;
use App\Repositories\FileRepository;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;
use DB;
use Sheba\Repositories\ProfileRepository;

class CoWorkerController extends Controller
{
    use CdnFileManager, FileManager;
    use ModificationFields;

    private $fileRepository;
    private $profileRepository;

    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository)
    {
        $this->fileRepository = $file_repository;
        $this->profileRepository = $profile_repository;
    }

    public function store($member, Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'mobile' => 'required|string|mobile:bd',
                'email' => 'required|email',
                #'department' => 'required|integer',
                'role' => 'required|integer',
                #'pro_pic' => 'required|mimes:jpeg,png',
                #'dob' => 'required|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                #'address' => 'required|string',

            ]);

            $member = Member::find($member);
            $this->setModifier($member);

            $profile = $this->profileRepository->checkExistingProfile($request->mobile, $request->email);

            if (!$profile) {
                $profile = $this->createProfile($member, $request);
                $new_member = $this->makeMember($profile);

                $business = $member->businesses->first();
                $member_business_data = [
                    'business_id' => $business->id,
                    'member_id' => $new_member->id,
                    'type' => 'Admin',
                    'join_date' => Carbon::now(),
                    #'department' => $request->department,
                    'business_role_id' => $request->role,
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));
            } else {
                $old_member = $profile->member;
                if (!$old_member) $new_member = $this->makeMember($profile);

                $business = $member->businesses->first();
                $member_business_data = [
                    'business_id' => $business->id,
                    'member_id' => $old_member ? $old_member->id : $new_member->id,
                    'type' => 'Admin',
                    'join_date' => Carbon::now(),
                    #'department' => $request->department,
                    'business_role_id' => $request->role,
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));
            }
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function departmentRole($business, Request $request)
    {
        try {
            $business = $request->business;
            $business_depts = BusinessDepartment::all();
            dd($business_depts->take(2));
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function createProfile($member, Request $request)
    {
        $this->setModifier($member);
        $profile_data = [
            'remember_token' => str_random(255),
            'mobile' => !empty($request->mobile) ? formatMobile($request->mobile) : null,
            'name' => $request->name,
            'email' => $request->email,
            ##'gender' => $request->gender,
            #'dob' => $request->dob,
            #'nid_no' => $request->nid_no,
            #'pro_pic' => $this->updateProfilePicture('pro_pic', $request->file('pro_pic')),
            #'address' => $request->address,
            #'driver_id' => $driver->id,
        ];

        return Profile::create($this->withCreateModificationField($profile_data));
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
}