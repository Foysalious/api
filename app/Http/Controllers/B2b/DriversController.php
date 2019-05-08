<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessMember;
use App\Models\BusinessTrip;
use App\Models\Driver;
use App\Models\Profile;
use App\Models\Vehicle;
use App\Models\VehicleRegistrationInformation;
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

class DriversController extends Controller
{
    use CdnFileManager, FileManager;
    use ModificationFields;

    private $fileRepository;
    private $profileRepository;

    public function __construct(FileRepository $file_repository , ProfileRepository $profile_repository)
    {
        $this->fileRepository = $file_repository;
        $this->profileRepository = $profile_repository;
    }

    public function store($member, Request $request)
    {
        try {
            $this->validate($request, [
                'license_number' => 'required',
                'license_number_image' => 'required|mimes:jpeg,png',
                'license_class' => 'required',
                'years_of_experience' => 'integer',

                'name' => 'required|string',
                'email' => 'email',
                #'mobile' => 'required|string|mobile:bd',
                'address' => 'required|string',
                'dob' => 'required|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                'nid_no' => 'required|integer',
                'pro_pic' => 'required|mimes:jpeg,png',

            ]);

            $member = Member::find($member);
            $this->setModifier($member);

            $driver_data = [
                'license_number' => $request->license_number,
                'license_number_image' => $this->updateDriversDocuments('license_number_image', $request->file('license_number_image')),
                'license_class' => $request->license_class,
                'years_of_experience' => $request->years_of_experience,
            ];
            $profile = $this->profileRepository->checkExistingMobile($request->mobile);
            $profile = $this->profileRepository->checkExistingEmail($request->email);
            dd($request->mobile, $request->email,$profile);
            $email_profile = Profile::where('email', $request->email)->first();
            #$driver = Driver::create($this->withCreateModificationField($driver_data));
            if (!$profile) {
                $profile = $this->createDriverProfile($member, $driver, $request);
                $new_member = $profile->member;
                if (!$new_member) $new_member = $this->makeMember($profile);

                $business = $member->businesses->first();
                $member_business_data = [
                    'business_id' => $business->id,
                    'member_id' => $new_member->id,
                    'type' => 'Admin',
                    'join_date' => Carbon::now(),
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));
            } else {
                $profile_data = [
                    'driver_id' => $driver->id,
                ];
                $new_member = $profile->member;
                if (!$new_member) $new_member = $this->makeMember($profile);

                $business = $member->businesses->first();
                $member_business_data = [
                    'business_id' => $business->id,
                    'member_id' => $new_member->id,
                    'type' => 'Admin',
                    'join_date' => Carbon::now(),
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));

                $profile->update($this->withCreateModificationField($profile_data));
            }

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function createDriverProfile($member, $driver, Request $request)
    {
        $this->setModifier($member);
        $profile_data = [
            'remember_token' => str_random(255),
            'mobile' => !empty($request->mobile) ? formatMobile($request->mobile) : null,
            'name' => $request->name,
            'driver_id' => $driver->id,
            'address' => $request->address,
            'email' => $request->email,
            #'gender' => $request->gender,
            'dob' => $request->dob,
            'nid_no' => $request->nid_no,
            'pro_pic' => $this->updateProfilePicture('pro_pic', $request->file('pro_pic')),
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

    public function update($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'license_number' => 'required',
                'license_number_image' => 'required|mimes:jpeg,png',
                'license_class' => 'required',
                'years_of_experience' => 'integer',
            ]);

            $member = Member::find($member);
            $this->setModifier($member);
            $driver = Driver::find((int)$driver);

            $driver_data = [
                'license_number' => $request->license_number,
                'license_number_image' => $this->updateDriversDocuments('license_number_image', $request->file('license_number_image')),
                'license_class' => $request->license_class,
                'years_of_experience' => $request->years_of_experience,
            ];
            $driver->update($this->withUpdateModificationField($driver_data));
            #$profile = Profile::where('mobile', formatMobile($request->mobile))->first();

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function driverLists($member, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            list($offset, $limit) = calculatePagination($request);
            $drivers = Driver::select('id', 'status')->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
                                #with('profile', 'vehicle', 'vehicle.basicInformations')->
            $driver_lists = [];
            foreach ($drivers as $driver) {
                $profile = $driver->profile;
                $vehicle = $driver->vehicle;
                $basic_information = $vehicle ? $vehicle->basicInformations : null;

                $driver = [
                    'id' => $driver->id,
                    'picture' => $profile->pro_pic,
                    'mobile' => $profile->mobile,
                    'status' => $driver->status,
                    'model_year' => $basic_information ? Carbon::parse($basic_information->model_year)->format('Y') : null,
                    'model_name' => $basic_information ? $basic_information->model_name : null,
                    'vehicle_type' => $basic_information ? $basic_information->type : null,
                ];
                array_push($driver_lists, $driver);
            }
            return api_response($request, $driver_lists, 200, ['driver_lists' => $driver_lists]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDriverGeneralInfo($member, $driver, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            #dd($driver->profile);

            $general_info = [
                'driver_id' => $driver->id,
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'pro_pic' => $profile->pro_pic,
                'dob' => Carbon::parse($profile->dob)->format('j M, Y'),
                #'blood_group' => $profile->blood_group,
                'nid_no' => $profile->nid_no,
            ];

            return api_response($request, $general_info, 200, ['general_info' => $general_info]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateDriverGeneralInfo($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'dob' => 'required|date|date_format:Y-m-d',
                'name' => 'string',
                'nid_no' => 'string',
            ]);
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $general_info = [
                'name' => $request->name,
                'dob' => $request->dob,
                #'blood_group' => $request->blood_group,
                'nid_no' => $request->nid_no,
            ];
            $profile->update($this->withUpdateModificationField($general_info));

            return api_response($request, 1, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDriverLicenseInfo($member, $driver, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $vehicle = $driver->vehicle;

            $license_info = [
                'type' => $vehicle ? $vehicle->basicInformations->type : null,
                'company_name' => $vehicle ? $vehicle->basicInformations->company_name : null,
                'model_name' => $vehicle ? $vehicle->basicInformations->model_name : null,
                'model_year' => $vehicle ? $vehicle->basicInformations->model_year : null,

                'license_number' => $driver->id,
                'license_class' => $profile->name,
                'issue_authority' => 'BRTA',
            ];

            return api_response($request, $license_info, 200, ['license_info' => $license_info]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateDriverLicenseInfo($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string|in:hatchback,sedan,suv,passenger_van,others',
                'company_name' => 'required|string',
                'model_name' => 'required|string',
                'model_year' => 'required|date|date_format:Y-m-d',
                'license_number' => 'required|string',
                'license_class' => 'required|string',
            ]);
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $vehicle = $driver->vehicle;

            $vehicle_basic_info = [
                'type' => $request->type,
                'company_name' => $request->company_name,
                'model_name' => $request->model_name,
                'model_year' => $request->model_year,
            ];
            if ($vehicle) $vehicle->basicInformations->update($this->withUpdateModificationField($vehicle_basic_info));

            $driver_info = [
                'license_number' => $request->license_number,
                'license_class' => $request->license_class,
            ];
            $driver->update($this->withUpdateModificationField($driver_info));

            return api_response($request, 1, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDriverContractInfo($member, $driver, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;

            $contract_info = [
                'email' => $profile->email,
                'mobile' => $profile->mobile,
                'address' => $profile->address,
            ];

            return api_response($request, $contract_info, 200, ['contract_info' => $contract_info]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateDriverContractInfo($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'mobile' => 'required|string|mobile:bd',
                'address' => 'required|string',
            ]);
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $exist_mobile = Profile::where('mobile', formatMobile($request->mobile))->first();
            $exist_email = Profile::where('email', $request->email)->first();

            if ($exist_mobile) {
                return response()->json(['message' => 'Mobile Number Already Exist.', 'code' => 403]);
            }
            if ($exist_email) {
                return response()->json(['message' => 'Email Already Exist.', 'code' => 403]);
            }
            $general_info = [
                'email' => $request->email,
                'mobile' => formatMobile($request->mobile),
                'address' => $request->address,
            ];
            $profile->update($this->withUpdateModificationField($general_info));

            return api_response($request, 1, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDriverExperienceInfo($member, $driver, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $vehicle = $driver->vehicle;

            $license_info = [
                'years_of_experience' => $driver->years_of_experience,
                'traffic_awareness' => $driver->traffic_awareness,
                'accident_history' => $driver->accident_history,
                'basic_knowledge' => $driver->basic_knowledge,
                'license_age_in_years' => (double)$driver->license_age_in_years,
                'additional_info' => $driver->additional_info,
            ];
            return api_response($request, $license_info, 200, ['license_info' => $license_info]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateDriverExperienceInfo($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'years_of_experience' => 'required|numeric',
                'traffic_awareness' => 'required|string',
                'accident_history' => 'required|string',
                'basic_knowledge' => 'required|string',
                'license_age_in_years' => 'required|numeric',
                'additional_info' => 'required|string',
            ]);
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $vehicle = $driver->vehicle;

            $driver_info = [
                'years_of_experience' => $request->years_of_experience,
                'traffic_awareness' => $request->traffic_awareness,
                'accident_history' => $request->accident_history,
                'basic_knowledge' => $request->basic_knowledge,
                'license_age_in_years' => $request->license_age_in_years,
                'additional_info' => $request->additional_info,
            ];
            $driver->update($this->withUpdateModificationField($driver_info));

            return api_response($request, 1, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDriverDocuments($member, $driver, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $vehicle = $driver->vehicle;

            $documents = [
                'license_number_image' => $driver->license_number_image,
            ];

            return api_response($request, $documents, 200, ['documents' => $documents]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateDriverDocuments($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'license_number_image' => 'required|mimes:jpeg,png',
            ]);
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $vehicle = $driver->vehicle;

            $documents = [
                'license_number_image' => $this->updateDriversDocuments('license_number_image', $request->file('license_number_image')),
            ];
            $driver->update($this->withUpdateModificationField($documents));

            return api_response($request, 1, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDriverRecentAssignment($member, $driver, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            $business_trips = BusinessTrip::where('driver_id', (int)$driver)->get();

            $recent_assignment = [];

            foreach ($business_trips as $business_trip) {
                $vehicle = $business_trip->vehicle;
                $basic_information = $vehicle ? $vehicle->basicInformations : null;

                $vehicle = [
                    'id' => $business_trip->id,
                    'status' => $business_trip->status,
                    'assigned_to' => 'ARNAD DADA',
                    'vehicle' => [
                        'type' => $basic_information->type,
                        'company_name' => $basic_information->company_name,
                        'model_name' => $basic_information->model_name,
                        'model_year' => $basic_information->model_year,
                    ]
                ];
                array_push($recent_assignment, $vehicle);
            }
            return api_response($request, $recent_assignment, 200, ['recent_assignment' => $recent_assignment]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function updateDriversDocuments($image_for, $photo)
    {
        $driver = new Driver();

        if (basename($driver->image_for) != 'default.jpg') {
            $filename = substr($driver->{$image_for}, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }

        $picture_link = $this->fileRepository->uploadToCDN($this->makeDocumentsName($driver, $photo, $image_for), $photo, 'images/drivers/' . $image_for . '_');
        return $picture_link;
    }

    private function updateProfilePicture($image_for, $photo)
    {
        $profile = new Profile();

        if (basename($profile->image_for) != 'default.jpg') {
            $filename = substr($profile->{$image_for}, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }

        $picture_link = $this->fileRepository->uploadToCDN($this->makePicName($profile, $photo, $image_for), $photo, 'images/profiles/' . $image_for . '_');
        return $picture_link;
    }

    private function makeDocumentsName(Driver $driver, $photo, $image_for = 'license_number_image')
    {
        return $filename = Carbon::now()->timestamp . '_' . $image_for . '_image_' . $driver->id . '.' . $photo->extension();
    }

    private function makePicName(Profile $profile, $photo, $image_for = 'license_number_image')
    {
        return $filename = Carbon::now()->timestamp . '_' . $image_for . '_image_' . $profile->id . '.' . $photo->extension();
    }

    private function deleteOldImage($filename)
    {
        $old_image = substr($filename, strlen(config('sheba.s3_url')));
        $this->fileRepository->deleteFileFromCDN($old_image);
    }

}
