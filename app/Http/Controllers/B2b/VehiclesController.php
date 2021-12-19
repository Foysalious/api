<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessTrip;
use App\Models\BusinessTripRequest;
use App\Models\HiredDriver;
use App\Models\HiredVehicle;
use App\Models\Partner;
use App\Models\Vehicle;
use App\Models\VehicleRegistrationInformation;
use App\Repositories\FileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Excel;
use Sheba\Business\Inspection\CreateProcessor;
use Sheba\Business\Inspection\Creator;
use Sheba\Business\Scheduler\TripScheduler;
use Sheba\Business\Vehicle\BulkUploadExcel;
use Sheba\Business\Vehicle\CreateRequest as VehicleCreateRequest;
use Sheba\Business\Vehicle\Creator as VehicleCreator;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;
use DB;
use Throwable;

class VehiclesController extends Controller
{
    use CdnFileManager, FileManager;
    use ModificationFields;

    const DUE_PERIOD = 60;
    const OVER_DUE_PERIOD = 0;

    private $fileRepository;

    public function __construct(FileRepository $file_repository)
    {
        $this->fileRepository = $file_repository;
    }

    /**
     * @param $member
     * @param Request $request
     * @param CreateProcessor $create_processor
     * @return JsonResponse
     */
    public function store($member, Request $request, CreateProcessor $create_processor)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string|in:hatchback,sedan,suv,passenger_van,others',
                'company_name' => 'required|string',
                'model_name' => 'required|string',
                'model_year' => 'required|date|date_format:Y-m-d',
                'seat_capacity' => 'required|integer',
                'transmission_type' => 'required|string|in:auto,manual',
                'vehicle_image' => 'file|mimes:jpeg,png',
                'license_number' => 'required|unique:vehicle_registration_informations',
                'license_number_end_date' => 'sometimes|date|date_format:Y-m-d',
                'license_number_image' => 'mimes:jpeg,png',
                'tax_token_number' => 'required|unique:vehicle_registration_informations',
                'tax_token_image' => 'mimes:jpeg,png',
                'fitness_start_date' => 'required|date|date_format:Y-m-d',
                'fitness_end_date' => 'required|date|date_format:Y-m-d',
                'fitness_paper_image' => 'mimes:jpeg,png',
                'insurance_date' => 'required|date|date_format:Y-m-d',
                'insurance_paper_image' => 'mimes:jpeg,png',
                'department_id' => 'required|integer',
            ]);

            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle_data = [
                'owner_type' => $request->filled('vendor_id') ? "App\Models\Partner" : get_class($business),
                'owner_id' => $request->filled('vendor_id') ? $request->vendor_id : $business->id,
                'business_department_id' => $request->department_id,
                'status' => 'active',
            ];
            if ($request->filled("driver_id")) {
                $vehicle_data['current_driver_id'] = $request->driver_id;
            }
            $vehicle = Vehicle::create($this->withCreateModificationField($vehicle_data));

            if ($request->filled('vendor_id')) {
                $data = [
                    'hired_by_type' => get_class($business),
                    'hired_by_id' => $business->id,
                    'owner_type' => "App\Models\Partner",
                    'owner_id' => $request->vendor_id,
                    'vehicle_id' => $vehicle->id,
                    'start' => Carbon::now()
                ];
                HiredVehicle::create($this->withCreateModificationField($data));
            }

            $vehicle_basic_information_data = [
                'type' => $request->type,
                'company_name' => $request->company_name,
                'model_name' => $request->model_name,
                'model_year' => $request->model_year,
                'seat_capacity' => $request->seat_capacity,
                'transmission_type' => $request->transmission_type,
                'vehicle_image' => $request->hasFile('vehicle_image') ? $this->updateVehicleImage($vehicle, $request->file('vehicle_image')) : null,
            ];

            $vehicle->basicInformations()->create($this->withCreateModificationField($vehicle_basic_information_data));

            $vehicle_registration_information_data = [
                'license_number' => $request->license_number,
                'license_number_end_date' => $request->license_number_end_date,
                'license_number_image' => $request->hasFile('license_number_image') ? $this->updateVehiclesDocuments('license_number_image', $request->file('license_number_image')) : '',
                'tax_token_number' => $request->tax_token_number,
                'tax_token_image' => $request->hasFile('tax_token_image') ? $this->updateVehiclesDocuments('tax_token_image', $request->file('tax_token_image')) : '',
                'fitness_start_date' => $request->fitness_start_date,
                'fitness_end_date' => $request->fitness_end_date,
                'fitness_paper_image' => $request->hasFile('fitness_paper_image') ? $this->updateVehiclesDocuments('fitness_paper_image', $request->file('fitness_paper_image')) : '',
                'insurance_date' => $request->insurance_date,
                'insurance_paper_image' => $request->hasFile('insurance_paper_image') ? $this->updateVehiclesDocuments('insurance_paper_image', $request->file('insurance_paper_image')) : '',
            ];
            $vehicle->registrationInformations()->create($this->withCreateModificationField($vehicle_registration_information_data));
            $inspection = null;
            if ($request->filled('form_template_id')) {
                $request->merge(['vehicle_id' => $vehicle->id, 'inspector_id' => $member->id, 'form_template_id' => $request->form_template_id,
                    'schedule_type_value' => date('Y-m-d'), 'schedule_time' => date('h a')]);
                /** @var Creator $creation_class */
                $creation_class = $create_processor->setType('one_time')->getCreationClass();
                $inspection = $creation_class->setData($request->all())->setBusiness($request->business)->create();
            }
            return api_response($request, $vehicle, 200, ['vehicle' => $vehicle->id, 'inspection_id' => $inspection ? $inspection->id : null]);
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
     * @param VehicleCreateRequest $create_request
     * @param VehicleCreator $creator
     * @return JsonResponse
     */
    public function bulkStore(Request $request, VehicleCreateRequest $create_request, VehicleCreator $creator)
    {
        try {
            $this->validate($request, ['file' => 'required|file']);

            $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
            $extension = $request->file('file')->getClientOriginalExtension();

            if (!in_array($extension, $valid_extensions)) {
                return api_response($request, null, 400, ['message' => 'File type not support']);
            }

            $admin_member = $request->member;
            $business = $admin_member->businesses->first();
            $this->setModifier($admin_member);

            $file = Excel::selectSheets(BulkUploadExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;

            $data = Excel::selectSheets(BulkUploadExcel::SHEET)->load($file_path)->get();

            $total_count = 0;
            $error_count = 0;
            $vehicle_type = BulkUploadExcel::VEHICLE_TYPE_COLUMN_TITLE;
            $vehicle_brand_name = BulkUploadExcel::VEHICLE_BRAND_NAME_COLUMN_TITLE;
            $model_name = BulkUploadExcel::MODEL_NAME_COLUMN_TITLE;
            $model_year = BulkUploadExcel::MODEL_YEAR_COLUMN_TITLE;
            $vehicle_department = BulkUploadExcel::VEHICLE_DEPARTMENT_COLUMN_TITLE;
            $seat_capacity = BulkUploadExcel::SEAT_CAPACITY_COLUMN_TITLE;
            $vendor_phone_number = BulkUploadExcel::VENDOR_PHONE_NUMBER_COLUMN_TITLE;
            $license_number = BulkUploadExcel::LICENSE_NUMBER_COLUMN_TITLE;
            $license_number_end_date = BulkUploadExcel::LICENSE_NUMBER_END_DATE_COLUMN_TITLE;
            $tax_token_number = BulkUploadExcel::TAX_TOKEN_NUMBER_COLUMN_TITLE;
            $fitness_validity_start = BulkUploadExcel::FITNESS_VALIDITY_START_COLUMN_TITLE;
            $fitness_validity_end = BulkUploadExcel::FITNESS_VALIDITY_END_COLUMN_TITLE;
            $insurance_valid_till = BulkUploadExcel::INSURANCE_VALID_TILL_COLUMN_TITLE;
            $transmission_type = BulkUploadExcel::TRANSMISSION_TYPE_COLUMN_TITLE;

            $data->each(function ($value) use (
                $create_request, $creator, $admin_member, &$error_count, &$total_count,
                $vehicle_type, $vehicle_brand_name, $model_name, $model_year, $vehicle_department,
                $seat_capacity, $vendor_phone_number, $license_number, $license_number_end_date, $tax_token_number, $fitness_validity_start,
                $fitness_validity_end, $insurance_valid_till, $transmission_type, $business
            ) {
                if (is_null($value->$vehicle_type) && is_null($value->$vehicle_brand_name)) return;
                $total_count++;
                if (!($value->$vehicle_type && $value->$vehicle_brand_name && $value->$vehicle_department)) {
                    $error_count++;
                    return;
                }

                /** @var VehicleCreateRequest $request */
                $create_request = $create_request->setVehicleType($value->$vehicle_type)
                    ->setVehicleBrandName($value->$vehicle_brand_name)
                    ->setModelName($value->$model_name)
                    ->setModelYear($value->$model_year)
                    ->setVehicleDepartment($value->$vehicle_department)
                    ->setSeatCapacity($value->$seat_capacity)
                    ->setVendorPhoneNumber($value->$vendor_phone_number)
                    ->setLicenseNumber($value->$license_number)
                    ->setLicenseNumberEndDate($value->$license_number_end_date)
                    ->setTaxTokenNumber($value->$tax_token_number)
                    ->setFitnessValidityStart($value->$fitness_validity_start)
                    ->setFitnessValidityEnd($value->$fitness_validity_end)
                    ->setInsuranceValidTill($value->$insurance_valid_till)
                    ->setTransmissionType($value->$transmission_type)
                    ->setBusiness($business)
                    ->setAdminMember($admin_member);

                $creator->setVehicleCreateRequest($create_request);
                if ($error = $creator->hasError()) {
                    $error_count++;
                } else {
                    $creator->create();
                }
            });

            $response_message = ($total_count - $error_count) . " Vehicle's Created Successfully, Failed {$error_count} vehicle's";
            return api_response($request, null, 200, ['message' => $response_message]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($member, $vehicle, Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'string|in:hatchback,sedan,suv,passenger_van,others',
                'company_name' => 'string',
                'model_name' => 'string',
                'model_year' => 'date|date_format:Y-m-d',
                'seat_capacity' => 'integer',
                'transmission_type' => 'string|in:auto,manual',
                'fitness_start_date' => 'required|date|date_format:Y-m-d',
                'fitness_end_date' => 'required|date|date_format:Y-m-d',
                'insurance_date' => 'date|date_format:Y-m-d',
                'license_number_end_date' => 'sometimes|date|date_format:Y-m-d',
                'department_id' => 'required|integer',
                #'fuel_type' => 'string',
                #'fuel_quality' => 'string',
                #'fuel_tank_capacity_ltr' => 'string',
                #'license_number' => 'required',
                #'license_number_image' => 'mimes:jpeg,png',
                #'tax_token_number' => 'required',
                #'tax_token_image' => 'mimes:jpeg,png',
                #'fitness_paper_image' => 'mimes:jpeg,png',
                #'insurance_paper_image' => 'mimes:jpeg,png'
            ]);

            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            $vehicle = Vehicle::find($vehicle);

            $vehicle_basic_informations = $vehicle->basicInformations;
            $vehicle_registration_informations = $vehicle->registrationInformations;

            $vehicle->update($this->withUpdateModificationField(['business_department_id' => $request->department_id]));

            $vehicle_basic_information_data = [
                'type' => $request->type,
                'company_name' => $request->company_name,
                'model_name' => $request->model_name,
                'model_year' => $request->model_year,
                'seat_capacity' => $request->seat_capacity,
                'transmission_type' => $request->transmission_type,
                #'fuel_type' => $request->fuel_type,
                #'fuel_quality' => $request->fuel_quality,
                #'fuel_tank_capacity_ltr' => $request->fuel_tank_capacity_ltr,
            ];
            if ($request->hasFile('vehicle_image')) {
                $vehicle_basic_information_data['vehicle_image'] = $this->updateVehicleImage($vehicle, $request->file('vehicle_image'));
            }
            $vehicle_basic_informations->update($this->withUpdateModificationField($vehicle_basic_information_data));

            $vehicle_registration_information_data = [
                'license_number' => $request->license_number,
                'license_number_end_date' => $request->license_number_end_date,
                'license_number_image' => $this->updateVehiclesDocuments('license_number_image', $request->file('license_number_image')),
                'tax_token_number' => $request->tax_token_number,
                'tax_token_image' => $this->updateVehiclesDocuments('tax_token_image', $request->file('tax_token_image')),
                'fitness_start_date' => $request->fitness_start_date,
                'fitness_end_date' => $request->fitness_end_date,
                'fitness_paper_image' => $this->updateVehiclesDocuments('fitness_paper_image', $request->file('fitness_paper_image')),
                'insurance_date' => $request->insurance_date,
                'insurance_paper_image' => $this->updateVehiclesDocuments('insurance_paper_image', $request->file('insurance_paper_image')),
            ];
            $vehicle_registration_informations->update($this->withUpdateModificationField($vehicle_registration_information_data));

            return api_response($request, $vehicle, 200, ['vehicle' => $vehicle->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $member
     * @param Request $request
     * @param TripScheduler $tripScheduler
     * @return JsonResponse
     */
    public function index($member, Request $request, TripScheduler $tripScheduler)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            if ($request->filled('trip_request_id')) {
                $business_trip_request = BusinessTripRequest::find((int)$request->trip_request_id);
                $car_ids = $tripScheduler->setStartDate($business_trip_request->start_date)->setEndDate($business_trip_request->end_date)
                    ->setBusinessDepartment($business_trip_request->member->businessMember->role->businessDepartment)
                    ->getFreeVehicles();
                $vehicles = Vehicle::whereIn('id', $car_ids->toArray())->with(['basicInformations', 'driver', 'registrationInformations'])->get();
            } else {
                list($offset, $limit) = calculatePagination($request);
                $vehicles = Vehicle::with(['basicInformations', 'registrationInformations'])
                    ->where(function ($q) use ($business) {
                        $hired_vehicles = $business->hiredVehicles()->with('vehicle')->active()->get()->pluck('vehicle.id');
                        $q->where('owner_id', $business->id)->orWhereIn('id', $hired_vehicles->toArray());
                    })->select('id', 'status', 'current_driver_id', 'business_department_id', 'owner_type', 'owner_id')
                    ->orderBy('id', 'desc')->skip($offset)->limit($limit);

                if ($request->filled('status'))
                    $vehicles = $vehicles->status($request->status);

                if ($request->filled('department')) {
                    $vehicles->where(function ($query) use ($request) {
                        $query->whereHas('businessDepartment', function ($query) use ($request) {
                            $query->where('name', $request->department);
                        });
                    });
                }

                if ($request->filled('type')) {
                    $vehicles->where(function ($query) use ($request) {
                        $query->whereHas('basicInformations', function ($query) use ($request) {
                            $query->where('type', $request->type);
                        });
                    });
                }
                if ($request->filled('owner_type')) {
                    if ($request->owner_type == 'own') $vehicles->whoseOwnerIsBusiness($business->id);
                    elseif ($request->owner_type == 'hired') $vehicles->whoseOwnerIsNotBusiness();
                }

                $vehicles = $vehicles->get();
            }
            $today = Carbon::now();
            $vehicle_lists = [];
            foreach ($vehicles as $vehicle) {
                $basic_information = $vehicle->basicInformations;
                $registration_information = $vehicle->registrationInformations ? $vehicle->registrationInformations : null;
                $driver = $vehicle->driver;

                $due_status = '';
                if (($vehicle->fitnessRemainingDays() <= VehiclesController::DUE_PERIOD && $vehicle->fitnessRemainingDays() > VehiclesController::OVER_DUE_PERIOD) ||
                    ($vehicle->insuranceRemainingDays() <= VehiclesController::DUE_PERIOD && $vehicle->insuranceRemainingDays() > VehiclesController::OVER_DUE_PERIOD) ||
                    ($vehicle->licenseRemainingDays() <= VehiclesController::DUE_PERIOD && $vehicle->licenseRemainingDays() > VehiclesController::OVER_DUE_PERIOD)) {
                    $due_status = 'Due Soon';
                }

                if ($vehicle->isFitnessDue() || $vehicle->isInsuranceDue() || $vehicle->isLicenseDue()) {
                    $due_status = 'Overdue';
                }

                $vehicle = [
                    'id' => $vehicle->id,
                    'vehicle_model' => $basic_information->model_name,
                    'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                    'status' => $vehicle->status,
                    'due_status' => $due_status,
                    'vehicle_type' => $basic_information->type,
                    'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    'current_driver' => $driver ? $vehicle->driver->profile->name : 'N/S',
                    'license_number' => $registration_information ? $registration_information->license_number : null,
                    'vehicle_image' => $basic_information->vehicle_image,
                    'is_own' => $vehicle->isOwn($business->id),
                ];
                array_push($vehicle_lists, $vehicle);
            }

            if (count($vehicle_lists) > 0) return api_response($request, $vehicle_lists, 200, ['vehicle_lists' => $vehicle_lists]);
            else  return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getVehicleGeneralInfo($member, $vehicle, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle = Vehicle::find((int)$vehicle);

            $basic_information = $vehicle->basicInformations;
            $registration_information = $vehicle->registrationInformations;

            $general_info = [
                'vehicle_id' => $vehicle->id,
                'model_year' => $basic_information->model_year,
                'company_name' => $basic_information->company_name,
                'model_name' => $basic_information->model_name,
                'type' => $basic_information->type,
                'status' => $vehicle->status,
                'enlisted_from' => $vehicle->created_at->format('d/m/Y'),
                'seat_capacity' => $basic_information->seat_capacity,
                'department' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                'vehicle_image' => $basic_information->vehicle_image,
            ];

            return api_response($request, $general_info, 200, ['general_info' => $general_info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getVehicleHandlers($member, $vehicle, Request $request)
    {
        try {

            $vehicle = Vehicle::find((int)$vehicle);
            $partner = $vehicle->owner;
            $general_info = [
                'driver' => $vehicle->driver ? [
                    'name' => $vehicle->driver->profile->name,
                    'mobile' => $vehicle->driver->profile->mobile,
                    'image' => $vehicle->driver->profile->pro_pic,
                ] : null,
                'vendor' =>
                    $partner instanceof Partner ? [
                        'name' => $partner->name,
                        'mobile' => $partner->getContactNumber(),
                        'logo' => $partner->logo,
                    ] : null

            ];
            return api_response($request, $general_info, 200, ['handler' => $general_info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateVehicleGeneralInfo($member, $vehicle, Request $request)
    {
        try {
            $this->validate($request, [
                'model_year' => 'required|date|date_format:Y-m-d',
                'company_name' => 'string',
                'model_name' => 'string',
                'type' => 'string|in:hatchback,sedan,suv,passenger_van,others',
                'seat_capacity' => 'required|integer',
                'department_id' => 'required|integer',
            ]);

            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle = Vehicle::find((int)$vehicle);

            $basic_information = $vehicle->basicInformations;
            $registration_information = $vehicle->registrationInformations;

            $vehicle->update($this->withUpdateModificationField(['business_department_id' => $request->department_id]));

            $basic_information_data = [
                'model_year' => $request->model_year,
                'company_name' => $request->company_name,
                'model_name' => $request->model_name,
                'type' => $request->type,
                'seat_capacity' => $request->seat_capacity
            ];
            if ($request->hasFile('vehicle_image')) {
                $basic_information_data['vehicle_image'] = $this->updateVehicleImage($vehicle, $request->file('vehicle_image'));
            }

            $basic_information->update($this->withUpdateModificationField($basic_information_data));

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getVehicleRegistrationInfo($member, $vehicle, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle = Vehicle::find((int)$vehicle);

            $basic_information = $vehicle->basicInformations;
            $registration_information = $vehicle->registrationInformations;

            $fitness_paper_due_status = '';
            $insurance_paper_due_status = '';
            $license_paper_due_status = '';

            if (($vehicle->fitnessRemainingDays() <= VehiclesController::DUE_PERIOD && $vehicle->fitnessRemainingDays() > VehiclesController::OVER_DUE_PERIOD)) {
                $fitness_paper_due_status = 'Due Soon';
            }

            if ($vehicle->isFitnessDue()) {
                $fitness_paper_due_status = 'Overdue';
            }

            if (($vehicle->insuranceRemainingDays() <= VehiclesController::DUE_PERIOD && $vehicle->insuranceRemainingDays() > VehiclesController::OVER_DUE_PERIOD)) {
                $insurance_paper_due_status = 'Due Soon';
            }

            if ($vehicle->isInsuranceDue()) {
                $insurance_paper_due_status = 'Overdue';
            }

            if (($vehicle->licenseRemainingDays() <= VehiclesController::DUE_PERIOD && $vehicle->licenseRemainingDays() > VehiclesController::OVER_DUE_PERIOD)) {
                $license_paper_due_status = 'Due Soon';
            }

            if ($vehicle->isLicenseDue()) {
                $license_paper_due_status = 'Overdue';
            }


            $registration_info = [
                'vehicle_id' => $vehicle->id,
                'license_number' => $registration_information->license_number,
                'license_number_end_date' => Carbon::parse($registration_information->license_number_end_date)->format('Y-m-d'),
                'license_number_image' => $registration_information->license_number_image,
                'tax_token_number' => $registration_information->tax_token_number,
                'tax_token_image' => $registration_information->tax_token_image,
                'registration_number' => $basic_information->license_number,
                'registration_number_image' => $basic_information->license_number_image,
                'fitness_start_date' => Carbon::parse($registration_information->fitness_start_date)->format('Y-m-d'),
                'fitness_end_date' => Carbon::parse($registration_information->fitness_end_date)->format('Y-m-d'),

                'fitness_paper_due_status' => $fitness_paper_due_status,

                'fitness_paper_image' => $registration_information->fitness_paper_image,
                'insurance_date' => Carbon::parse($registration_information->insurance_date)->format('Y-m-d'),

                'insurance_paper_due_status' => $insurance_paper_due_status,
                'insurance_paper_image' => $registration_information->insurance_paper_image,

                'license_paper_due_status' => $license_paper_due_status,
            ];
            return api_response($request, $registration_info, 200, ['registration_info' => $registration_info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateVehicleRegistrationInfo($member, $vehicle, Request $request)
    {
        try {
            $this->validate($request, [
                'license_number' => 'string',
                'license_number_end_date' => 'sometimes|date|date_format:Y-m-d',
                'tax_token_number' => 'string',
                'fitness_start_date' => 'required|date|date_format:Y-m-d',
                'fitness_end_date' => 'required|date|date_format:Y-m-d',
                'insurance_date' => 'required|date|date_format:Y-m-d'
            ]);

            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle = Vehicle::find((int)$vehicle);

            $basic_information = $vehicle->basicInformations;
            $registration_information = $vehicle->registrationInformations;

            $registration_information_data = [
                'license_number' => $request->license_number,
                'license_number_end_date' => $request->license_number_end_date,
                'tax_token_number' => $request->tax_token_number,
                'fitness_start_date' => $request->fitness_start_date,
                'fitness_end_date' => $request->fitness_end_date,
                'insurance_date' => $request->insurance_date,
            ];

            $registration_information->update($this->withUpdateModificationField($registration_information_data));

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getVehicleSpecs($member, $vehicle, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle = Vehicle::find((int)$vehicle);
            if (!$vehicle) return api_response($request, null, 404);

            $basic_information = $vehicle->basicInformations;
            $registration_information = $vehicle->registrationInformations;

            $specs_info = [
                'height_inch' => (double)$basic_information->height_inch,
                'width_inch' => (double)$basic_information->width_inch,
                'length_inch' => (double)$basic_information->length_inch,
                'volume_ft' => (double)$basic_information->volume_ft,
                'weight_kg' => (double)$basic_information->weight_kg,
                'max_payload_kg' => (double)$basic_information->max_payload_kg,
                'engine_summary' => $basic_information->engine_summary,
                'transmission_type' => $basic_information->transmission_type,
            ];

            return api_response($request, $specs_info, 200, ['specs_info' => $specs_info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateVehicleSpecs($member, $vehicle, Request $request)
    {
        try {
            $this->validate($request, [
                'height_inch' => 'required|numeric',
                'width_inch' => 'required|numeric',
                'length_inch' => 'required|numeric',
                'volume_ft' => 'required|numeric',
                'weight_kg' => 'required|numeric',
                'max_payload_kg' => 'required|numeric',
                'engine_summary' => 'required|string',
                'transmission_type' => 'required|string|in:auto,manual',
            ]);
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle = Vehicle::find((int)$vehicle);
            if (!$vehicle) return api_response($request, null, 404);

            $basic_information = $vehicle->basicInformations;

            $basic_information_data = [
                'height_inch' => $request->height_inch,
                'width_inch' => $request->width_inch,
                'length_inch' => $request->length_inch,
                'volume_ft' => $request->volume_ft,
                'weight_kg' => $request->weight_kg,
                'max_payload_kg' => $request->max_payload_kg,
                'engine_summary' => $request->engine_summary,
                'transmission_type' => $request->transmission_type,
            ];
            $basic_information->update($this->withUpdateModificationField($basic_information_data));

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getVehicleRecentAssignment($member, $vehicle, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            $business_trips = BusinessTrip::where('vehicle_id', (int)$vehicle)->get();

            $recent_assignment = [];
            foreach ($business_trips as $business_trip) {
                $vehicle = [
                    'id' => $business_trip->id,
                    'status' => $business_trip->vehicle->status,
                    'assigned_to' => $business_trip->member ? $business_trip->member->profile->name : null,
                    'driver' => $business_trip->driver->profile ? $business_trip->driver->profile->name : 'N/S',
                ];
                array_push($recent_assignment, $vehicle);
            }
            return api_response($request, $recent_assignment, 200, ['recent_assignment' => $recent_assignment]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function updateVehiclesDocuments($image_for, $photo)
    {
        $vehicle_registration_information = new VehicleRegistrationInformation();

        if (basename($vehicle_registration_information->image_for) != 'default.jpg') {
            $filename = substr($vehicle_registration_information->{$image_for}, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }

        #$picture_link = $this->fileRepository->uploadToCDN($this->makePicName($vehicle_registration_information, $photo, $image_for), $photo, 'images/vehicles/' . $image_for . '_');
        $picture_link = $this->fileRepository->uploadToCDN($this->makePicName($vehicle_registration_information, $photo, $image_for), $photo, 'images/profiles/' . $image_for . '_');
        return $picture_link;
    }

    private function updateVehicleImage(Vehicle $vehicle, $photo)
    {
        if (!empty($vehicle->vehicle_image) && basename($vehicle->vehicle_image) != 'default.jpg') {
            $filename = substr($vehicle->vehicle_image, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }
        $link = $this->fileRepository->uploadToCDN($this->makeVehicleImageName($vehicle, $photo), $photo, 'images/profiles/vehicles/');
        return $link;
    }

    private function deleteOldImage($filename)
    {
        $old_image = substr($filename, strlen(config('sheba.s3_url')));
        $this->fileRepository->deleteFileFromCDN($old_image);
    }

    private function makePicName(VehicleRegistrationInformation $vehicle_registration_information, $photo, $image_for = 'license_number_image')
    {
        return $filename = Carbon::now()->timestamp . '_' . $image_for . '_image_' . $vehicle_registration_information->id . '.' . $photo->extension();
    }

    private function makeVehicleImageName(Vehicle $vehicle, $photo)
    {
        return $filename = Carbon::now()->timestamp . '_vehicle_image_' . $vehicle->id . '.' . $photo->extension();
    }

    public function unTagVehicleDriver($member, $vehicle, Request $request)
    {
        try {
            $vehicle = Vehicle::find((int)$vehicle);

            if (!$vehicle) return api_response($request, 1, 404);

            if (!$request->driver_id) {
                $vehicle->update(['current_driver_id' => null]);
            } else {
                $vehicle->update(['current_driver_id' => $request->driver_id]);
            }


            return api_response($request, 1, 200);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
