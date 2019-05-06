<?php namespace App\Http\Controllers\B2b;

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
                #'type' => 'required|string|in:car,bus,bike,cycle',
                'company_name' => 'required|string',
                'model_name' => 'required|string',
                'model_year' => 'required|date|date_format:Y-m-d',
                'seat_capacity' => 'required|integer',
                'transmission_type' => 'required|string|in:auto,manual',
                'fuel_type' => 'required|string',
                'fuel_quality' => 'required|string',
                'fuel_tank_capacity_ltr' => 'required|string',

                'license_number' => 'required',
                'license_number_image' => 'required|mimes:jpeg,png',
                'tax_token_number' => 'required',
                'tax_token_image' => 'required|mimes:jpeg,png',
                'fitness_start_date' => 'required|date|date_format:Y-m-d',
                'fitness_end_date' => 'required|date|date_format:Y-m-d',
                'fitness_paper_image' => 'required|mimes:jpeg,png',
                'insurance_date' => 'required|date|date_format:Y-m-d',
                'insurance_paper_image' => 'required|mimes:jpeg,png',
            ]);

            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $vehicle_data = [
                'owner_type' => get_class($business),
                'owner_id' => $business->id,
            ];
            $vehicle = Vehicle::create($this->withCreateModificationField($vehicle_data));

            $vehicle_basic_information_data = [
                'type' => $request->type,
                'company_name' => $request->company_name,
                'model_name' => $request->model_name,
                'model_year' => $request->model_year,
                'seat_capacity' => $request->seat_capacity,
                'transmission_type' => $request->transmission_type,
                'fuel_type' => $request->fuel_type,
                'fuel_quality' => $request->fuel_quality,
                'fuel_tank_capacity_ltr' => $request->fuel_tank_capacity_ltr,
            ];
            $vehicle->basicInformations()->create($this->withCreateModificationField($vehicle_basic_information_data));

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
            $vehicle->registrationInformations()->create($this->withCreateModificationField($vehicle_registration_information_data));

            return api_response($request, 1, 200);
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
                'type' => 'string|in:car,bus,bike,cycle',
                'company_name' => 'string',
                'model_name' => 'string',
                'model_year' => 'date|date_format:Y-m-d',
                'seat_capacity' => 'integer',
                'transmission_type' => 'string|in:auto,manual',
                'fuel_type' => 'string',
                'fuel_quality' => 'string',
                'fuel_tank_capacity_ltr' => 'string',

                #'license_number' => 'required',
                'license_number_image' => 'mimes:jpeg,png',
                #'tax_token_number' => 'required',
                'tax_token_image' => 'mimes:jpeg,png',
                'fitness_start_date' => 'required|date|date_format:Y-m-d',
                'fitness_end_date' => 'required|date|date_format:Y-m-d',
                'fitness_paper_image' => 'mimes:jpeg,png',
                'insurance_date' => 'date|date_format:Y-m-d',
                'insurance_paper_image' => 'mimes:jpeg,png'
            ]);

            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);
            $vehicle = Vehicle::find($vehicle);

            $vehicle_basic_informations = $vehicle->basicInformations;
            $vehicle_registration_informations = $vehicle->registrationInformations;

            $vehicle_basic_information_data = [
                'type' => $request->type,
                'company_name' => $request->company_name,
                'model_name' => $request->model_name,
                'model_year' => $request->model_year,
                'seat_capacity' => $request->seat_capacity,
                'transmission_type' => $request->transmission_type,
                'fuel_type' => $request->fuel_type,
                'fuel_quality' => $request->fuel_quality,
                'fuel_tank_capacity_ltr' => $request->fuel_tank_capacity_ltr,
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

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function vehicleLists($member, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            list($offset, $limit) = calculatePagination($request);
            $vehicles = Vehicle::select('id', 'status')->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();

            $vehicle_lists = [];
            foreach($vehicles as $vehicle){
                $basic_information = $vehicle->basicInformations;
                $registration_information = $vehicle->registrationInformations;
                dd($vehicle->drivers);
                $vehicle = [
                    'vehicle_model' => $basic_information->model_name,
                    'status' => $vehicle->status,
                    'vehicle_type' => $basic_information->type,
                    'assigned_to' => 1,
                    'driver' => 1,
                ];
                array_push($vehicle_lists, $vehicle);
            }
            return api_response($request, $vehicle_lists, 200, ['vehicle_lists' => $vehicle_lists]);
        } catch (\Throwable $e) {
            dd($e);
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
