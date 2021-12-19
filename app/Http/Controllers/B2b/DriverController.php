<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessTrip;
use App\Models\BusinessTripRequest;
use App\Models\Driver;
use App\Models\HiredDriver;
use App\Models\Profile;
use App\Models\Vehicle;

use App\Repositories\FileRepository;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\Business\Driver\BulkUploadExcel;
use Sheba\Business\Driver\CreateRequest;
use Sheba\Business\Driver\Creator;
use Sheba\Business\Scheduler\TripScheduler;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;
use DB;
use Sheba\Repositories\ProfileRepository;
use Throwable;
use Excel;

class DriverController extends Controller
{
    use CdnFileManager, FileManager;
    use ModificationFields;

    const DUE_PERIOD = 60;
    const OVER_DUE_PERIOD = 0;

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
                'license_number' => 'required',
                'license_number_end_date' => 'required|date|date_format:Y-m-d',
                'license_number_image' => 'sometimes|required|mimes:jpeg,png',
                'license_class' => 'required',
                'years_of_experience' => 'integer',
                'name' => 'required|string',
                'mobile' => 'required|string|mobile:bd',
                'address' => 'required|string',
                'dob' => 'required|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                'nid_no' => 'required|integer',
                'pro_pic' => 'sometimes|required|mimes:jpeg,png',
                'role_id' => 'required|integer',
                'nid_image_front' => 'sometimes|required|mimes:jpeg,png',
                'nid_image_back' => 'sometimes|required|mimes:jpeg,png',
            ]);
            $member = $request->member;
            $this->setModifier($member);

            $driver_data = [
                'status' => 'active',
                'license_number' => $request->license_number,
                'license_number_end_date' => $request->license_number_end_date,
                'license_number_image' => $request->hasFile('license_number_image') ? $this->updateDriversDocuments('license_number_image', $request->file('license_number_image')) : '',
                'license_class' => $request->license_class,
                'years_of_experience' => $request->years_of_experience,
            ];

            $profile = $this->profileRepository->checkExistingMobile($request->mobile);
            if (!$profile) {
                $driver = Driver::create($this->withCreateModificationField($driver_data));
                $profile = $this->createDriverProfile($member, $driver, $request);
                $new_member = $profile->member;
                if (!$new_member) $new_member = $this->makeMember($profile);
                $business = $member->businesses->first();
                #$business_department = BusinessDepartment::find((int)$request->department_id);
                #$business_role = $business_department->businessRoles()->where('name', 'like', '%Driver%')->first();
                $member_business_data = [
                    'business_id' => $business->id,
                    'member_id' => $new_member->id,
                    'type' => 'Admin',
                    'join_date' => Carbon::now(),
                    'business_role_id' => $request->role_id,
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));
            } else {
                $driver = $profile->driver;
                if (!$driver) {
                    $driver = Driver::create($this->withCreateModificationField($driver_data));
                    $profile_data = ['driver_id' => $driver->id];
                    $profile->update($this->withCreateModificationField($profile_data));

                    $new_member = $profile->member;
                    if (!$new_member) $new_member = $this->makeMember($profile);

                    $business = $member->businesses->first();
                    #$business_department = BusinessDepartment::find((int)$request->department_id);
                    #$business_role = $business_department->businessRoles()->where('name', 'like', '%Driver%')->first();
                    $member_business_data = [
                        'business_id' => $business->id,
                        'member_id' => $new_member->id,
                        'type' => 'Admin',
                        'join_date' => Carbon::now(),
                        'business_role_id' => $request->role_id,
                    ];
                    BusinessMember::create($this->withCreateModificationField($member_business_data));
                } else {
                    return api_response($request, null, 403, ['message' => 'Driver already exits!']);
                }
            }

            if ($request->filled('vehicle_id')) {
                $vehicle = Vehicle::find((int)$request->vehicle_id);
                $vehicle->current_driver_id = $driver->id;
                $vehicle->save();
            }

            if ($request->filled('vendor_id')) {
                $data = [
                    'hired_by_type' => get_class($business),
                    'hired_by_id' => $business->id,
                    'owner_type' => "App\Models\Partner",
                    'owner_id' => $request->vendor_id,
                    'driver_id' => $driver->id,
                    'start' => Carbon::now()
                ];
                HiredDriver::create($this->withCreateModificationField($data));
            }

            return api_response($request, $driver, 200, ['driver' => $driver->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param CreateRequest $create_request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function bulkStore(Request $request, CreateRequest $create_request, Creator $creator)
    {
        try {
            $this->validate($request, ['file' => 'required|file']);

            $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
            $extension = $request->file('file')->getClientOriginalExtension();

            if (!in_array($extension, $valid_extensions)) {
                return api_response($request, null, 400, ['message' => 'File type not support']);
            }

            $admin_member = $request->member;
            $this->setModifier($admin_member);

            $file = Excel::selectSheets(BulkUploadExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;

            $data = Excel::selectSheets(BulkUploadExcel::SHEET)->load($file_path)->get();

            $total_count = 0;
            $error_count = 0;
            $license_number_field = BulkUploadExcel::LICENSE_NUMBER_COLUMN_TITLE;
            $license_number_end_date_field = BulkUploadExcel::LICENSE_NUMBER_END_DATE_COLUMN_TITLE;
            $license_class = BulkUploadExcel::LICENSE_CLASS_COLUMN_TITLE;
            $driver_mobile = BulkUploadExcel::PHONE_NUMBER_COLUMN_TITLE;
            $name = BulkUploadExcel::DRIVER_NAME_COLUMN_TITLE;
            $date_of_birth = BulkUploadExcel::DATE_OF_BIRTH_COLUMN_TITLE;
            $blood_group = BulkUploadExcel::BLOOD_GROUP_COLUMN_TITLE;
            $nid_number = BulkUploadExcel::NID_NUMBER_COLUMN_TITLE;
            $department = BulkUploadExcel::DRIVER_DEPARTMENT_COLUMN_TITLE;
            $vendor_mobile = BulkUploadExcel::VENDOR_PHONE_NUMBER_COLUMN_TITLE;
            $driver_role = BulkUploadExcel::DRIVER_ROLE_COLUMN_TITLE;
            $driver_address = BulkUploadExcel::ADDRESS_COLUMN_TITLE;

            $data->each(function ($value) use (
                $create_request, $creator, $admin_member, &$error_count, &$total_count,
                $license_number_field, $license_number_end_date_field, $license_class, $driver_mobile, $name, $date_of_birth, $blood_group,
                $nid_number, $department, $vendor_mobile, $driver_role, $driver_address
            ) {
                if (is_null($value->$name) && is_null($value->$driver_mobile)) return;
                $total_count++;
                if (!($value->$name && $value->$driver_mobile)) {
                    $error_count++;
                    return;
                }

                /** @var CreateRequest $request */
                $create_request = $create_request->setMobile($value->$driver_mobile)
                    ->setLicenseNumber($value->$license_number_field)
                    ->setLicenseNumberEndDate($value->$license_number_end_date_field)
                    ->setLicenseClass($value->$license_class)
                    ->setName($value->$name)
                    ->setDateOfBirth($value->$date_of_birth)
                    ->setBloodGroup($value->$blood_group)
                    ->setNidNumber($value->$nid_number)
                    ->setDepartment($value->$department)
                    ->setVendorMobile($value->$vendor_mobile)
                    ->setRole($value->$driver_role)
                    ->setAddress($value->$driver_address)
                    ->setAdminMember($admin_member);

                $creator->setDriverCreateRequest($create_request);
                if ($error = $creator->hasError()) {
                    $error_count++;
                } else {
                    $creator->create();
                }
            });

            $response_message = ($total_count - $error_count) . " Driver's Created Successfully, Failed {$error_count} driver's";
            return api_response($request, null, 200, ['message' => $response_message]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
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
            'nid_image_front' => $request->hasFile('nid_image_front') ? $this->updateProfilesPicture('nid_image_front', $request->file('nid_image_front')) : '',
            'nid_image_back' => $request->hasFile('nid_image_back') ? $this->updateProfilesPicture('nid_image_back', $request->file('nid_image_back')) : '',
            'pro_pic' => $request->hasFile('pro_pic') ? $this->updateProfilesPicture('pro_pic', $request->file('pro_pic')) : '',
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
                'license_number_end_date' => 'required|date|date_format:Y-m-d',
                'license_number_image' => 'required|mimes:jpeg,png',
                'license_class' => 'required',
                'years_of_experience' => 'integer',
            ]);

            $member = Member::find($member);
            $this->setModifier($member);
            $driver = Driver::find((int)$driver);

            $driver_data = [
                'license_number' => $request->license_number,
                'license_number_end_date' => $request->license_number_end_date,
                'license_number_image' => $this->updateDriversDocuments('license_number_image', $request->file('license_number_image')),
                'license_class' => $request->license_class,
                'years_of_experience' => $request->years_of_experience,
            ];
            $driver->update($this->withUpdateModificationField($driver_data));

            return api_response($request, $driver, 200, ['driver' => $driver->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function index($member, Request $request, TripScheduler $vehicleScheduler)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            if ($request->filled('trip_request_id')) {
                $business_trip_request = BusinessTripRequest::find((int)$request->trip_request_id);
                $driver_ids = $vehicleScheduler->setStartDate($business_trip_request->start_date)->setEndDate($business_trip_request->end_date)->setBusiness($request->business)
                    ->getFreeDrivers();
                $drivers = Driver::whereIn('id', $driver_ids->toArray())->with('profile')->get();
            } else {
                list($offset, $limit) = calculatePagination($request);
                $drivers = Driver::whereHas('profile', function ($q) use ($business) {
                    $q->WhereHas('member', function ($q) use ($business) {
                        $q->whereHas('businesses', function ($q) use ($business) {
                            $q->where('businesses.id', $business->id);
                        });
                    });
                })->orWhereHas('hiredBy', function ($q) use ($business) {
                    $q->whichIsHiredByBusiness($business->id);
                })->with('profile', 'vehicle.basicInformation')->orderBy('id', 'desc')->skip($offset)->limit($limit);

                if ($request->filled('status'))
                    $drivers = $drivers->status($request->status);

                if ($request->filled('type')) {
                    $drivers->where(function ($query) use ($request) {
                        $query->whereHas('vehicle.basicInformations', function ($query) use ($request) {
                            $query->where('type', $request->type);
                        });
                    });
                }
                $drivers = $drivers->get();
            }
            $driver_lists = [];
            foreach ($drivers as $driver) {
                $profile = $driver->profile;
                $vehicle = $driver->vehicle;
                $basic_information = $vehicle ? $vehicle->basicInformations : null;

                $due_status = '';
                if (($driver->licenseRemainingDays() <= DriverController::DUE_PERIOD && $driver->licenseRemainingDays() > DriverController::OVER_DUE_PERIOD)) {
                    $due_status = 'Due Soon';
                }
                if ($driver->isLicenseDue()) {
                    $due_status = 'Overdue';
                }
                $driver = [
                    'id' => $driver->id,
                    'name' => $profile->name,
                    'picture' => $profile->pro_pic,
                    'mobile' => $profile->mobile,
                    'status' => $driver->status,
                    'license_number_end_date' => Carbon::parse($driver->license_number_end_date)->format(''),
                    'due_status' => $due_status,
                    'model_year' => $basic_information ? Carbon::parse($basic_information->model_year)->format('Y') : null,
                    'model_name' => $basic_information ? $basic_information->model_name : null,
                    'vehicle_type' => $basic_information ? $basic_information->type : null,
                ];
                array_push($driver_lists, $driver);
            }
            if (count($driver_lists) > 0) return api_response($request, $driver_lists, 200, ['driver_lists' => $driver_lists]);
            else  return api_response($request, null, 404);
        } catch (Throwable $e) {
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

            $general_info = [
                'driver_id' => $driver->id,
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'pro_pic' => $profile->pro_pic,
                'dob' => Carbon::parse($profile->dob)->format('j M, Y'),
                #'blood_group' => $profile->blood_group,
                'nid_no' => $profile->nid_no,
                "nid_image_front" => $profile->nid_image_front,
                "nid_image_back" => $profile->nid_image_back
            ];

            return api_response($request, $general_info, 200, ['general_info' => $general_info]);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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

            $due_status = '';
            if (($driver->licenseRemainingDays() <= DriverController::DUE_PERIOD && $driver->licenseRemainingDays() > DriverController::OVER_DUE_PERIOD)) {
                $due_status = 'Due Soon';
            }

            if ($driver->isLicenseDue()) {
                $due_status = 'Overdue';
            }

            $license_info = [
                'type' => $vehicle ? $vehicle->basicInformations->type : null,
                'vehicle_image' => $vehicle ? $vehicle->basicInformations->vehicle_image : null,
                'vehicle_model_name' => $vehicle ? $vehicle->basicInformations->model_name : null,
                'vehicle_license_number' => $vehicle ? $vehicle->registrationInformations->license_number : null,
                #'company_name' => $vehicle ? $vehicle->basicInformations->company_name : null,
                #'model_name' => $vehicle ? $vehicle->basicInformations->model_name : null,
                #'model_year' => $vehicle ? $vehicle->basicInformations->model_year : null,
                'department_id' => $profile->member->businessMember->role ? $profile->member->businessMember->role->business_department_id : null,
                'license_number' => $driver->license_number,
                'due_status' => $due_status,
                'license_number_end_date' => Carbon::parse($driver->license_number_end_date)->format('Y-m-d'),
                'license_number_image' => $driver->license_number_image,
                'license_class' => $driver->license_class,
                'issue_authority' => 'BRTA',
            ];

            return api_response($request, $license_info, 200, ['license_info' => $license_info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateDriverLicenseInfo($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string|in:hatchback,sedan,suv,passenger_van,others',
                #'company_name' => 'required|string',
                #'model_name' => 'required|string',
                #'model_year' => 'required|date|date_format:Y-m-d',
                'license_number' => 'required|string',
                'license_number_end_date' => 'required|date|date_format:Y-m-d',
                'license_class' => 'required|string',
                'department_id' => 'required|integer',
            ]);
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            if (!$driver) return api_response($request, null, 404);
            $profile = $driver->profile;
            $vehicle = $driver->vehicle;

            $business_department = BusinessDepartment::find((int)$request->department_id);
            $business_role = $business_department->businessRoles()->where('name', 'like', '%Driver%')->first();

            if ($business_role) $business_role->update($this->withUpdateModificationField(['business_department_id' => $request->department_id]));

            $vehicle_basic_info = [
                'type' => $request->type,
                #'company_name' => $request->company_name,
                #'model_name' => $request->model_name,
                #'model_year' => $request->model_year,
            ];
            if ($vehicle) $vehicle->basicInformations->update($this->withUpdateModificationField($vehicle_basic_info));

            $driver_info = [
                'license_number' => $request->license_number,
                'license_number_end_date' => $request->license_number_end_date,
                'license_class' => $request->license_class,
            ];
            $driver->update($this->withUpdateModificationField($driver_info));

            return api_response($request, 1, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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

    private function updateProfilesPicture($image_for, $photo)
    {
        $profile = new Profile();

        if (!empty($profile->{$image_for}) && basename($profile->{$image_for}) != 'default.jpg') {
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

    public function updatePicture($member, $driver, Request $request, \App\Repositories\ProfileRepository $profileRepository)
    {
        try {
            $driver = Driver::find($driver);
            $profile = $driver->profile;
            $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id . '.jpg';
            $profile->pro_pic = $this->fileRepository->uploadToCDN($filename, $request->file('image'), 'images/profiles/');
            $profile->update();
            return api_response($request, $profile, 200);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
