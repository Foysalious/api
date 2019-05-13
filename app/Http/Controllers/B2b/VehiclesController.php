<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessTrip;
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

class VehiclesController extends Controller
{
    use CdnFileManager, FileManager;
    use ModificationFields;

    private $fileRepository;

    public function __construct(FileRepository $file_repository)
    {
        $this->fileRepository = $file_repository;
    }

    public function store($member, Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string|in:hatchback,sedan,suv,passenger_van,others',
                'company_name' => 'required|string',
                'model_name' => 'required|string',
                'model_year' => 'required|date|date_format:Y-m-d',
                'seat_capacity' => 'required|integer',
                'transmission_type' => 'required|string|in:auto,manual',
                #'fuel_type' => 'required|string',
                #'fuel_quality' => 'required|string',
                #'fuel_tank_capacity_ltr' => 'required|string',

                'license_number' => 'required|unique:vehicle_registration_informations',
                #'license_number_image' => 'required|mimes:jpeg,png',
                'tax_token_number' => 'required|unique:vehicle_registration_informations',
                #'tax_token_image' => 'required|mimes:jpeg,png',
                'fitness_start_date' => 'required|date|date_format:Y-m-d',
                'fitness_end_date' => 'required|date|date_format:Y-m-d',
                #'fitness_paper_image' => 'required|mimes:jpeg,png',
                'insurance_date' => 'required|date|date_format:Y-m-d',
                #'insurance_paper_image' => 'required|mimes:jpeg,png',
                'department_id' => 'required|integer',
            ]);

            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle_data = [
                'owner_type' => get_class($business),
                'owner_id' => $business->id,
                'department_id' => $request->department_id,
            ];
            $vehicle = Vehicle::create($this->withCreateModificationField($vehicle_data));

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
            $vehicle->basicInformations()->create($this->withCreateModificationField($vehicle_basic_information_data));

            $vehicle_registration_information_data = [
                'license_number' => $request->license_number,
                #'license_number_image' => $this->updateVehiclesDocuments('license_number_image', $request->file('license_number_image')),
                'tax_token_number' => $request->tax_token_number,
                #'tax_token_image' => $this->updateVehiclesDocuments('tax_token_image', $request->file('tax_token_image')),
                'fitness_start_date' => $request->fitness_start_date,
                'fitness_end_date' => $request->fitness_end_date,
                #'fitness_paper_image' => $this->updateVehiclesDocuments('fitness_paper_image', $request->file('fitness_paper_image')),
                'insurance_date' => $request->insurance_date,
                #'insurance_paper_image' => $this->updateVehiclesDocuments('insurance_paper_image', $request->file('insurance_paper_image')),
            ];
            $vehicle->registrationInformations()->create($this->withCreateModificationField($vehicle_registration_information_data));

            return api_response($request, $vehicle, 200, ['vehicle' => $vehicle->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
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
                #'fuel_type' => 'string',
                #'fuel_quality' => 'string',
                #'fuel_tank_capacity_ltr' => 'string',

                #'license_number' => 'required',
                #'license_number_image' => 'mimes:jpeg,png',
                #'tax_token_number' => 'required',
                #'tax_token_image' => 'mimes:jpeg,png',
                'fitness_start_date' => 'required|date|date_format:Y-m-d',
                'fitness_end_date' => 'required|date|date_format:Y-m-d',
                #'fitness_paper_image' => 'mimes:jpeg,png',
                'insurance_date' => 'date|date_format:Y-m-d',
                #'insurance_paper_image' => 'mimes:jpeg,png'
                'department_id' => 'required|integer',
            ]);

            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            $vehicle = Vehicle::find($vehicle);

            $vehicle_basic_informations = $vehicle->basicInformations;
            $vehicle_registration_informations = $vehicle->registrationInformations;

            $vehicle->update($this->withUpdateModificationField(['department_id' => $request->department_id]));

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
            $vehicle_basic_informations->update($this->withUpdateModificationField($vehicle_basic_information_data));

            $vehicle_registration_information_data = [
                'license_number' => $request->license_number,
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function index($member, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            list($offset, $limit) = calculatePagination($request);
            $vehicles = Vehicle::with('basicInformations')->where('owner_id', $business->id)->select('id', 'status', 'current_driver_id', 'department_id')->orderBy('id', 'desc')->skip($offset)->limit($limit);

            if ($request->has('status'))
                $vehicles = $vehicles->status($request->status);

            if ($request->has('type')) {
                $vehicles->where(function ($query) use ($request) {
                    $query->whereHas('basicInformations', function ($query) use ($request) {
                        $query->where('type', $request->type);
                    });
                });
            }
            $vehicles = $vehicles->get();
            $vehicle_lists = [];
            foreach ($vehicles as $vehicle) {
                $basic_information = $vehicle->basicInformations;
                $driver = $vehicle->driver;
                $vehicle = [
                    'id' => $vehicle->id,
                    'vehicle_model' => $basic_information->model_name,
                    'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                    'status' => $vehicle->status,
                    'vehicle_type' => $basic_information->type,
                    'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    'current_driver' => $driver ? $vehicle->driver->profile->name : 'N/S',
                ];
                array_push($vehicle_lists, $vehicle);
            }

            if (count($vehicle_lists) > 0) return api_response($request, $vehicle_lists, 200, ['vehicle_lists' => $vehicle_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
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
            ];

            return api_response($request, $general_info, 200, ['general_info' => $general_info]);
        } catch (\Throwable $e) {
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

            $vehicle->update($this->withUpdateModificationField(['department_id' => $request->department_id]));

            $basic_information_data = [
                'model_year' => $request->model_year,
                'company_name' => $request->company_name,
                'model_name' => $request->model_name,
                'type' => $request->type,
                'seat_capacity' => $request->seat_capacity,
            ];

            $basic_information->update($this->withUpdateModificationField($basic_information_data));

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
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

            $registration_info = [
                'vehicle_id' => $vehicle->id,
                'license_number' => $registration_information->license_number,
                'tax_token_number' => $registration_information->tax_token_number,
                'fitness_start_date' => Carbon::parse($registration_information->fitness_start_date)->format('Y-m-d'),
                'fitness_end_date' => Carbon::parse($registration_information->fitness_end_date)->format('Y-m-d'),
                'insurance_date' => Carbon::parse($registration_information->insurance_date)->format('Y-m-d'),
            ];
            return api_response($request, $registration_info, 200, ['registration_info' => $registration_info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateVehicleRegistrationInfo($member, $vehicle, Request $request)
    {
        try {
            $this->validate($request, [
                'license_number' => 'string',
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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

    private function deleteOldImage($filename)
    {
        $old_image = substr($filename, strlen(config('sheba.s3_url')));
        $this->fileRepository->deleteFileFromCDN($old_image);
    }

    private function makePicName(VehicleRegistrationInformation $vehicle_registration_information, $photo, $image_for = 'license_number_image')
    {
        return $filename = Carbon::now()->timestamp . '_' . $image_for . '_image_' . $vehicle_registration_information->id . '.' . $photo->extension();
    }

}
