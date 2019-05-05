<?php namespace App\Http\Controllers\B2b;

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

class DriversController extends Controller
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
                'license_number' => 'required',
                'license_number_image' => 'required|mimes:jpeg,png',
                'license_class' => 'required',
                'years_of_experience' => 'required|integer',
            ]);

            $member = Member::find($member);
            $this->setModifier($member);

            $driver_data = [
                'license_number' => $request->license_number,
                'license_number_image' => $this->updateDocuments('license_number_image', $request->file('license_number_image')),
                'license_class' => $request->license_class,
                'years_of_experience' => $request->years_of_experience,
            ];
            $driver = Driver::create($this->withCreateModificationField($driver_data));
            $profile = Profile::where('mobile', formatMobile($request->mobile))->first();
            if (!$profile){
                $this->createDriverProfile($member, $driver, $request);
            }else{
                $profile_data = [
                    'driver_id' => $driver->id,
                ];
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
            'address' => $request->address,
            'email' => $request->email,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'nid_no' => $request->nid_no,
            'pro_pic' => $request->pro_pic,
        ];

        return Profile::create($this->withCreateModificationField($profile_data));
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
                'fitness_date' => 'date|date_format:Y-m-d',
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

            #dd($vehicle_basic_informations, $vehicle_registration_informations);

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
                'fitness_date' => $request->fitness_date,
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
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function updateDocuments($image_for, $photo)
    {
        $vehicle_registration_information = new VehicleRegistrationInformation();

        if (basename($vehicle_registration_information->image_for) != 'default.jpg') {
            $filename = substr($vehicle_registration_information->{$image_for}, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }

        #$picture_link = $this->fileRepository->uploadToCDN($this->makePicName($vehicle_registration_information, $photo, $image_for), $photo, 'images/drivers/' . $image_for . '_');
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
